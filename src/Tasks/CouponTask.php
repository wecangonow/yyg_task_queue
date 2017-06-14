<?php
/**
 * Created by PhpStorm.
 * User: og
 * Date: 17/6/8
 * Time: 上午11:09
 */

namespace Yyg\Tasks;

class CouponTask implements TaskInterface
{
    public static function execute(array $task)
    {
        $category = $task['argv']['category'];

        switch($category) {
            case "register":
                self::register($task);
                break;
        }
    }

    public static function register($task)
    {
        global $db;
        $uid = $task['argv']['uid'];
        $sql = "select * from sp_coupon_type where id = 9 or id = 10";
        $type_info = $db->query($sql);

        if(count($type_info) > 1) {
            $insert_id = 0;
            foreach($type_info as $v) {
                $insert_id = $db->insert("sp_user_coupon")->cols(
                    [
                        'uid' => $uid,
                        'coupon_id' => 1,
                        'title' => $v['title'],
                        'category' => $task['argv']['category'],
                        'sub_title' => $v['sub_title'],
                        'type' => $v['type'],
                        'full_amount' => $v['full_amount'],
                        'minus_amount' => $v['minus_amount'],
                        'expired_time' => time() + $v['expired_days'] * 24 *3600,
                        'create_time' => time()

                    ]
                )->query();

                if($insert_id){
                    mdebug("new register user got coupon record id is %d", $insert_id);
                }


            }

            if($insert_id != 0) {
                $sql = "select reg_token, `group` from sp_reg_token where uid = $uid";
                $tokens = $db->query($sql);
                if(count($tokens) > 0) {
                    $android_tokens = [];
                    foreach($tokens as $v) {
                        if($v['group'] == "" || $v['group'] == "android") {
                            $android_tokens[] = $v['reg_token'];
                        }
                    }

                    $title        = "You've received Free Lucky Coins Coupon!";
                    $message      = "Use right now or it will expire in 24 hours.";
                    $send_message = ['title' => $title, 'message' => $message];

                    NoticeTask::send_gcm_notify($android_tokens, $send_message);
                }


            }
        }

    }

    
}