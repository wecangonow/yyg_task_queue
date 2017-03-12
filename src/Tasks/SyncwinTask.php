<?php
/**
 * Created by PhpStorm.
 * User: og
 * Date: 17/3/9
 * Time: 下午3:28
 */

namespace Yyg\Tasks;

class SyncwinTask implements TaskInterface
{
    public static function execute(array $task)
    {
        global $redis, $configs;

        $uid     = $task['argv']['uid'];
        $price   = $task['argv']['price'];
        $nper_id = $task['argv']['nper_id'];

        $key = str_replace("{uid}", $uid, $configs['prize']['user_life_win_key_scheme']);

        $redis->executeRaw(['incrby', $key, $price]);
        minfo("uid %s win nper %d added %d to user_win_life", $uid, $nper_id, $price);
    }
    
}
