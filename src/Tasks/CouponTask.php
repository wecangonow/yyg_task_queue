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

        switch ($category) {
            case "register":
                self::register($task);
                break;
            case "init_red_coupon":
                self::init_red_coupon($task);
                break;
            case "init_user":
                self::init_red_coupon($task);
                break;
        }
    }

    public static function init_user($task)
    {
        global $redis, $configs;

        $nper_id = $task['argv']['nper_id'];

        $nper_coupon_user_key = str_replace(
            "{nid}",
            $nper_id,
            $configs['coupon']['nper_bonus_total']
        );

    }

    public static function init_red_coupon($task)
    {
        global $db;
        $pid     = $task['argv']['pid'];
        $nper_id = $task['argv']['nper_id'];

        $sql = "select coupon_data from sp_goods where id = $pid";
        $ret = $db->row($sql);

        if (isset($ret['coupon_data'])) {
            $coupon_config = $ret['coupon_data'];
            //$coupon_config = '[{"id":"1","coupon_num":"3"},{"id":"2","coupon_num":"5"}]';
            $config_arr = json_decode($coupon_config, true);
            foreach ($config_arr as $v) {
                $coupon_id  = $v['id'];
                $coupon_num = $v['coupon_num'];
                $ids        = self::insert_coupon($coupon_id, $coupon_num);
                self::cache_coupon_info_per_nper($ids, $nper_id);
            }
        }
        else {
            merror("goods %d not have coupon config", $pid);
        }

    }

    public static function cache_coupon_info_per_nper($ids, $nper_id)
    {
        global $redis;

        $key = "red_coupon_ids#" . $nper_id;

        foreach($ids as $id) {
            $redis->zadd($key,0,$id);
            mdebug("nper %d add coupon id %d", $nper_id, $id);
        }
    }

    public static function insert_coupon($coupon_id, $num)
    {
        global $db;
        $sql       = "select * from sp_coupon_type where id = $coupon_id";
        $type_info = $db->row($sql);
        $ret       = [];
        for ($i = 0; $i < $num; $i++) {
            $insert_id = $db->insert("sp_user_coupon")->cols(
                [
                    'coupon_id'    => $coupon_id,
                    'title'        => $type_info['title'],
                    'category'     => "red_coupon",
                    'sub_title'    => $type_info['sub_title'],
                    'type'         => $type_info['type'],
                    'full_amount'  => $type_info['full_amount'],
                    'minus_amount' => $type_info['minus_amount'],
                    'expired_time' => time() + $type_info['expired_days'] * 24 * 3600,
                    'create_time'  => time(),

                ]
            )->query();

            $ret[] = $insert_id;
        }

        return $ret;
    }

    public static function register($task)
    {
        global $db;
        $uid       = $task['argv']['uid'];
        $sql       = "select * from sp_coupon_type where id = 9 or id = 10";
        $type_info = $db->query($sql);

        if (count($type_info) > 1) {
            $insert_id = 0;
            foreach ($type_info as $v) {
                $insert_id = $db->insert("sp_user_coupon")->cols(
                    [
                        'uid'          => $uid,
                        'coupon_id'    => $v['id'],
                        'title'        => $v['title'],
                        'category'     => $task['argv']['category'],
                        'sub_title'    => $v['sub_title'],
                        'type'         => $v['type'],
                        'full_amount'  => $v['full_amount'],
                        'minus_amount' => $v['minus_amount'],
                        'expired_time' => time() + $v['expired_days'] * 24 * 3600,
                        'create_time'  => time(),

                    ]
                )->query();

                if ($insert_id) {
                    mdebug("new register user got coupon record id is %d", $insert_id);
                }

            }

            if ($insert_id != 0) {
                $sql    = "select reg_token, `group` from sp_reg_token where uid = $uid";
                $tokens = $db->query($sql);
                if (count($tokens) > 0) {
                    $android_tokens = [];
                    foreach ($tokens as $v) {
                        if ($v['group'] == "" || $v['group'] == "android") {
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