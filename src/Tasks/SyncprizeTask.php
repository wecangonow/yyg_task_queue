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
    //同步工作包括， 同步机器人id集合 , 同步单用户历史真实入账集合, 同步已产生第一单用户集合
    public static function execute(array $task)
    {
        global $db, $redis, $configs;

        $sql = "select id from `sp_users` where type = -1";


        $ids = $db->query($sql);

        $key = $configs['prize']['robot_set'];

        foreach($ids as $id) {
            mdebug("add id %d to redis set %s", $id['id'], $key);
            $redis->executeRaw(['sadd', $key, $id['id']]);
        }


        $sql = "select  o.uid, l.create_time, l.paid from log_notify l join `sp_order_list_parent` o on l.order_id = o.order_id where state = 'completed' ";

        $rows = $db->query($sql);


        if(count($rows)) {
            foreach($rows as $row) {
                self::addUserPeriodConsumerToCache(
                    str_replace("{uid}", $row['uid'], $configs['prize']['user_period_consume_key_scheme']),
                    strtotime($row['create_time']),
                    $row['paid']
                );
            }

        }

        $sql = "select  DISTINCT o.uid from `sp_order_list_parent` o join sp_users u on o.uid = u.id  where o.pay_status = 3 and o.bus_type ='buy' and u.type = 1 ";

        $key = $configs['prize']['already_consume_user_key_scheme'];
        $rows = $db->query($sql);
        if(count($rows)) {
            foreach($rows as $row) {
                $redis->executeRaw(['sadd', $key, $row['uid']]);
                mdebug("add uid %d to redis set %s", $row['uid'], $key);
            }

        }

    }
    
    public static function addUserPeriodConsumerToCache($key, $score, $value)
    {
        global $redis;

        $redis->executeRaw(['zadd', $key, $score, $value]);

    }
}