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
        $id = $task['argv']['coupon_id'];

        $sql = "select expired_days from sp_coupon_type where id = (select coupon_id from sp_user_coupon where id = $id)";

        $ret = $db->row($sql);

        $expired_days = $ret['expired_days'];

        $expired_time = time() + 3600*24*$expired_days;

        $sql = "update sp_user_coupon set uid = $uid, expired_time = $expired_time where id = $id";

        $update_result = $db->query($sql);

        mdebug("sync update sql is %s : result %s", $sql, serialize($update_result));
    }
    
}