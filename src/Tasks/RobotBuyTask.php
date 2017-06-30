<?php
/**
 * Created by PhpStorm.
 * User: og
 * Date: 17/6/28
 * Time: 下午6:09
 */

namespace Yyg\Tasks;

class RobotBuyTask implements TaskInterface
{
    public static function execute(array $task)
    {
        global $configs;

        $request_url = $configs['robot_buy_url'];

        $request_data = $task['argv']['request_data'];
        $gid          = $request_data['gid'];
        $task_detail  = $task['argv']['task_detail'];
        $robot_info = $task['argv']['robot_info'];

        mdebug("I buying one");



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

    public static function sync_task($task)
    {

    }
    
}
