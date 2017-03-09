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
        global $db;

        $uid = $task['argv']['uid'];

        $sql = "select id from `sp_user_prize_statistics` where id = $uid";

        $row = $db->row($sql);

        if(empty($row)) {
            $score = $db->row("select score from sp_users where id = $uid");
        }

        echo $score . "\n";

    }
    
}