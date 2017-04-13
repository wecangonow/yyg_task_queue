<?php

/**
 * Created by PhpStorm.
 * User: og
 * Date: 17/4/1
 * Time: 下午6:09
 */
namespace Yyg\Common;
class Api
{
    public static function getUserBonusState(array $task)
    {
        global $redis, $configs, $db;

        $uid = $task['argv']['uid'];

        $key = str_replace(
            "{uid}",
            $uid,
            $configs['bonus']['user_every_nper_get_bonus_state']
        );

        $npers = $redis->zrangebyscore($key, 0, 0);
        //$npers = [5555, 4402];
        if(count($npers) > 0) {
            $condition = rtrim(implode(",", $npers),",");
            $condition = "(" . $condition  . ")";
            $sql = "select open_time from sp_nper_list where id in $condition";
            $open_times = $db->query($sql);
            foreach($open_times as $time) {
                if($time['open_time'] <= time()) {
                    return json_encode(true);
                }
            }

            return json_encode(false);

        } else {
            return json_encode(false);
        }

    }

    public static function getUserNperBonusState(array $task)
    {
        global $redis, $configs;

        $uid = $task['argv']['uid'];
        $nper_id = $task['argv']['nper_id'];

        $key = str_replace(
            "{uid}",
            $uid,
            $configs['bonus']['user_every_nper_get_bonus_state']
        );

        $score = $redis->zscore($key, $nper_id);

        mdebug("key %s score %s", $key, $score);
        if(is_null($score)) {
            return json_encode(false);
        } else {
            if($score == 0) {
                return json_encode(true);
            } else{
                return json_encode(false);
            } 
        }
    }
    
}
