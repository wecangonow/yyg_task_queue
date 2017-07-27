<?php
/**
 * Created by PhpStorm.
 * User: og
 * Date: 17/3/9
 * Time: 下午3:28
 */

namespace Yyg\Tasks;

use Yyg\Core\ExecutionTime;
use \Workerman\Lib\Timer;
use \Workerman\Events\Select;
use \Workerman\Autoloader;

class SyncwinTask implements TaskInterface
{
    public static function execute(array $task)
    {
        ExecutionTime::Start();

        global $redis, $configs, $db;

        $nper_id = $task['argv']['nper_id'];


        $sql = "select w.luck_uid, g.price, u.type from sp_win_record w join sp_goods g on g.id = w.goods_id  join sp_users u on u.id = w.luck_uid where w.nper_id = $nper_id";


        $ret = $db->row($sql);

        if(!empty($ret)) {

            if($ret['type'] != -1) {

                $val = $nper_id . "_" . $ret['price'];

                $key = str_replace("{uid}", $ret['luck_uid'], $configs['prize']['user_life_win_key_scheme']);

                $add_result = $redis->executeRaw(['sadd', $key, $val]);

                if($add_result) {
                    minfo("uid %s win nper %d added %d to user_win_life", $ret['luck_uid'], $nper_id, $ret['price']);
                }

                $redis->set("cold_down_count:key#" . $ret['luck_uid'], 5);
                minfo("user %d cold_down count set to 5", $ret['luck_uid']);

                // 真人中奖用户注册一个4天不填地址发邮件和app通知任务
                $run_time = time() + 3600 * 24 * 4;
                $check_confirm_address = ['type' => 'notice', 'argv' => ['category' => 'confirm_address', 'run_time' => $run_time, 'nper_id' => $nper_id, 'luck_uid' => $ret['luck_uid']]];
                $task_data = json_encode($check_confirm_address);
                $redis->lpush("slow_queue", $task_data);
                minfo("got task: %s", $task_data);


            } else {
                minfo("uid %s is a robot do not update nper_id %d - price %d user win life", $ret['luck_uid'], $nper_id, $ret['price']);
            }

        } else {

            $back_message = json_encode($task);
            merror("syncwin failed %s ", $back_message);

        }


        ExecutionTime::End();

        minfo("%s::execute spend %s data is %s ", get_called_class(), ExecutionTime::ExportTime(), json_encode($task));
    }



}
