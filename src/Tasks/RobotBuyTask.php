<?php
/**
 * Created by PhpStorm.
 * User: og
 * Date: 17/6/28
 * Time: 下午6:09
 */

namespace Yyg\Tasks;

use Yyg\Core\ExecutionTime;

class RobotBuyTask implements TaskInterface
{

    public static function execute(array $task)
    {
        ExecutionTime::Start();

        global $configs;

        $request_url = $configs['robot_buy_url'];

        $request_data = $task['argv']['request_data'];

        $gid          = $request_data['gid'];
        $task_detail  = $task['argv']['task_detail'];
        $robot_info = $task['argv']['robot_info'];

        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', $request_url, [
            'form_params' => $request_data
        ]);
        $body = $response->getBody();
        $ret = json_decode($body, true);

        if (isset($ret['1']) && $ret['1'] == "success") {

            $nper_id = $ret['nper_id'];

            $ignore_setting = AutoBuyCheckTask::getTaskCurrentNperIdAndIgnorePercent($gid, $nper_id);

            if ($ignore_setting) {
                $ignore_setting_nper_id = explode("_", $ignore_setting)[0];
                if ($ignore_setting_nper_id != $nper_id) {
                    AutoBuyCheckTask::setTaskCurrentNperIdAndIgnorePercent($gid, $nper_id);
                }
            }

            minfo(
                "task id %d : excuted delayed %d s robot %d buy goods %d %d times unit_price %d",
                $task_detail['id'],
                time() - $task_detail['exec_time'],
                $robot_info['id'],
                $task_detail['gid'],
                $task_detail['buy_times'],
                $task_detail['unit_price']
            );
            AutoBuyCheckTask::sync_task($task_detail);
            self::write_remote_log($nper_id, $robot_info, $task_detail['buy_times']);
        } else {
            mdebug("task execute failed %s", json_encode($request_data));
        }

        ExecutionTime::End();
        minfo("%s::execute spend %s ", get_called_class(), ExecutionTime::ExportTime());

    }

    public static function write_remote_log($nper_id, $rt = [], $num)
    {
        global $db;
        $sql = "select g.name from sp_nper_list n left join sp_goods g on n.pid = g.id where n.id = $nper_id";
        $g_name = $db->row($sql)['name'];


        $time = time();
        $log  = '机器人(' . $rt['nick_name'] . ')于' . date("Y-m-d H:i:s", $time) . '购买了(' . $g_name . ')' . $num . '份';
        $data = ['nper_id'     => $nper_id,
                 'user'        => $rt['id'],
                 'type'        => 'RtRegular',
                 'log'         => $log,
                 'create_time' => $time,
        ];
        $insert_id = $db->insert("log")->cols($data)->query();
        mdebug("robot buy log id is %d", $insert_id);
    }
}
