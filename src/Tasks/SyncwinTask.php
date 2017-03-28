<?php
/**
 * Created by PhpStorm.
 * User: og
 * Date: 17/3/9
 * Time: 下午3:28
 */

namespace Yyg\Tasks;

use Yyg\Core\ExecutionTime;

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

            if($ret['type'] != 1) {

                $val = $nper_id . "_" . $ret['price'];

                $key = str_replace("{uid}", $ret['luck_uid'], $configs['prize']['user_life_win_key_scheme']);

                $add_result = $redis->executeRaw(['sadd', $key, $val]);

                if($add_result) {
                    minfo("uid %s win nper %d added %d to user_win_life", $ret['luck_uid'], $nper_id, $ret['price']);
                }

            } else {
                minfo("uid %s is a robot do not update nper_id %d - price %d user win life", $ret['luck_uid'], $nper_id, $ret['price']);
            }

        } else {

            $back_message = json_encode($task);
            merror("syncwin failed %s ", $back_message);

        }

        ExecutionTime::End();

        minfo("%s::execute spend %s ", get_called_class(), ExecutionTime::ExportTime());
    }


}
