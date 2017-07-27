<?php
/**
 * Created by PhpStorm.
 * User: og
 * Date: 17/7/27
 * Time: 下午5:51
 */

namespace Yyg\Tasks;

class InitCDTask implements TaskInterface
{
    public static function execute(array $task)
    {
        global $db, $redis;

        $sql = "select distinct(luck_uid) as uid from sp_win_record w join sp_users u on u.id = w.luck_uid where u.type = 1";

        $user_ids = $db->query($sql);

        if(count($user_ids) > 0) {
            foreach($user_ids as $v) {
                $key = "cold_down_count:key#" . $v['uid'];

                $redis->set($key, 5);

                minfo("%s value set to 5", $key);
            }
        }

    }
    
}