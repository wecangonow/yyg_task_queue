<?php
/**
 * Created by PhpStorm.
 * User: og
 * Date: 17/4/1
 * Time: 上午11:08
 */

namespace Yyg\Tasks;

class BonusSyncTask implements TaskInterface
{
    public static function execute(array $task)
    {
        global $db;

        $uid = $task['argv']['uid'];
        $amount = $task['argv']['amount'];
        $create_time = time();

        $sql = "insert into sp_user_money (uid, money, create_time, type) values ($uid, $amount, $create_time, 6)";

        $insert_result = $db->query($sql);

        mdebug("sync insert sql is %s : result %s ", $sql, serialize($insert_result));

        $update = "update sp_users set money = money + $amount where id = $uid";

        $update_result = $db->query($update);

        mdebug("sync update sql is %s : result %s", $sql, serialize($update_result));
    }
    
}