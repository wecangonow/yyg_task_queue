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
        global $redis, $configs;

        $uid = $task['argv']['uid'];

        $key = str_replace(
            "{uid}",
            $uid,
            $configs['bonus']['user_every_nper_get_bonus_state']
        );

        $score = $redis->zcount($key, 0,0);

        mdebug("key %s score %s", $key, $score);
        if($score > 0) {
            return json_encode(true);
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
