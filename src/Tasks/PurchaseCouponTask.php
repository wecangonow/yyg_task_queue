<?php

namespace Yyg\Tasks;

class PurchaseCouponTask implements TaskInterface
{
    public static function execute(array $task)
    {
        global $redis, $configs;

        $nper_id        = $task['argv']['nper_id'];
        $uid            = $task['argv']['uid'];
        $coupon_set_key = "red_coupon_ids#" . $nper_id;

        $user_nper_get_bonus_record = str_replace(
            "{uid}",
            $uid,
            $configs['bonus']['user_every_nper_get_bonus_state']
        );

        $state = $redis->executeRaw(['zscore', $user_nper_get_bonus_record, $nper_id]);

        if ((int)$state == 0) {  // 没有抢
        }

    }

    public static function updateUserCoupon($uid, $coupon_id)
    {
        global $db;
        $sql = "update sp_user_coupon set uid = $uid where id = $coupon_id";
        $ret = $db->query($sql);

    }
    
}