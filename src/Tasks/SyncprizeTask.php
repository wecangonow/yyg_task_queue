<?php
/**
 * Created by PhpStorm.
 * User: og
 * Date: 17/3/9
 * Time: 下午3:28
 */

namespace Yyg\Tasks;

class SyncprizeTask implements TaskInterface
{
    public static function execute(array $task)
    {
        global $db, $redis, $configs;


        $sql = "select  o.uid, l.create_time, l.paid from log_notify l join `sp_order_list_parent` o on l.order_id = o.order_id where state = 'completed' ";

        $rows = $db->query($sql);

        $key = $configs['prize']['already_consume_user_key_scheme'];

        if(count($rows)) {
            foreach($rows as $row) {
                self::addUserPeriodConsumerToCache(
                    str_replace("{uid}", $row['uid'], $configs['prize']['user_period_consume_key_scheme']),
                    strtotime($row['create_time']),
                    $row['paid']
                );
                $redis->executeRaw(['sadd', $key, $row['uid']]);
                mdebug("add uid %d to redis set %s", $row['uid'], $key);
            }

        }

        $sql = "select id from `sp_users` type = -1";

        $ids = $db->query($sql);

        $key = $configs['prize']['robot_set'];

    }
    
    public static function addUserPeriodConsumerToCache($key, $score, $value)
    {
        global $redis;

        $redis->executeRaw(['zadd', $key, $score, $value]);

    }
}