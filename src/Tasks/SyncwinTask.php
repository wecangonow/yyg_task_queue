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


        $sql = "select w.luck_uid, g.price from sp_win_record w join sp_goods g on g.id = w.goods_id where w.nper_id = $nper_id";

        $ret = $db->row($sql);

        if(!empty($ret)) {

            $key = str_replace("{uid}", $ret['luck_uid'], $configs['prize']['user_life_win_key_scheme']);
            $redis->executeRaw(['incrby', $key, $ret['price']]);
            minfo("uid %s win nper %d added %d to user_win_life", $ret['luck_uid'], $nper_id, $ret['price']);
        } else {

            $back_message = json_encode($task);

            $redis->lpush("message_queue", $back_message);
        }

        ExecutionTime::End();

        minfo("%s::execute spend %s ", get_called_class(), ExecutionTime::ExportTime());
    }


}
