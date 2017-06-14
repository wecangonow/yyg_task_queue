<?php

namespace Yyg\Tasks;

class NoticeTask implements TaskInterface
{
    public static function execute(array $task)
    {
        global $configs, $redis;

        $category = $task['argv']['category'];

        $tokens   = [];
        $continue = true;
        switch ($category) {

            case "coupon_expired":
                self::coupon_expired($task);
                $continue = false;
                break;
            case "confirm_address":
                $run_time = $task['argv']['run_time'];
                $nper_id  = $task['argv']['nper_id'];
                $uid      = $task['argv']['luck_uid'];
                if (($run_time - time()) > 0) {
                    $redis->lpush("slow_queue", json_encode($task));
                }
                else {
                    self::confirm_address($nper_id, $uid, $task);
                }
                $continue = false;
                break;
            case "nocheckin":
                $tokens = $task['argv']['tokens'];
                foreach ($tokens as $k => $v) {
                    if (!self::verify_limit($v)) {
                        unset($tokens[$k]);
                    }
                }
                break;
            case "show_participate":
                $show_order_ids = trim($task['argv']['show_order_ids'], ',');
                $tokens         = self::get_token_with_show_order_ids($show_order_ids);
                break;
            case "shipped":
                $win_record_id = $task['argv']['win_record_id'];
                $tokens        = self::get_token_with_win_record_id($win_record_id);
                break;
            case "winning_bonus":
                $nper_id = $task['argv']['nper_id'];
                self::send_winning_bonus_push($nper_id);
                $continue = false;
                break;
        }

        if ($continue) {
            $title        = $configs['services']['android_push']['tpl'][$category]['title'];
            $message      = $configs['services']['android_push']['tpl'][$category]['message'];
            $send_message = ['title' => $title, 'message' => $message];

            self::send_gcm_notify($tokens, $send_message);
        }
        else {
            mdebug("notice not continue");
        }
    }

    public static function coupon_expired($task)
    {
        global $configs, $db;
        $email = $task['argv']['email'];
        $token = $task['argv']['reg_token'];
        $ids   = $task['argv']['uid'];

        if ($token) {
            if (self::verify_limit($token)) {
                $title        = $configs['services']['android_push']['tpl']['coupon_expired']['title'];
                $message      = $configs['services']['android_push']['tpl']['coupon_expired']['message'];
                $tokens       = [$token];
                $send_message = ['title' => $title, 'message' => $message];
                self::send_gcm_notify($tokens, $send_message);
            }
        }

        if ($email) {
            if (self::verify_limit($email)) {
                $email_config                  = $configs['services']['email'];
                $real_email_content['subject'] = $email_config['info']['tpl']['coupon_expired']['subject'];
                //$real_email_content['body']    = preg_replace('/\s+/u', ' ', $email_config['info']['tpl']['coupon_expired']['body']);
                $real_email_content['body']    = $email_config['info']['tpl']['coupon_expired']['body'];
                $real_email_content['is_html'] = $email_config['info']['tpl']['coupon_expired']['is_html'];
                $real_email_content['email']   = $email;

                $sql = "select g.name,  s.username from sp_show_order s join sp_goods g on g.id = s.goods_id where s.status = 1 order by s.id desc limit 4";
                $info = $db->query($sql);
                $append_str = "\n";
                if(count($info) > 0) {
                    foreach($info as $v) {
                        $append_str .= $v['username'] . " + " . $v['name'] . "\n";
                    }
                }

                $real_email_content['body'] = str_replace("{{share_info}}", $append_str, $real_email_content['body']);

                EmailTask::send_email($real_email_content, $task);

            }
        }

        $con = "(" . implode(",", $ids) . ")";
        $sql = "update sp_user_coupon set noticed = 1 where id in $con";
        $db->query($sql);
    }

    public static function confirm_address($nper_id, $uid, $task)
    {
        global $db, $configs;
        $sql = "select id from sp_prize_status where win_record_id = (select id from sp_win_record where nper_id = $nper_id) and status = 'confirm_address'";

        $ret = $db->query($sql);

        if (count($ret) > 0) {
            minfo("nper %d user have confirm address", $nper_id);
        }
        else {
            minfo("nper %d user have not confirm address", $nper_id);
            $sql = "select reg_token from sp_reg_token where uid = $uid order by id desc limit 1";

            $ret = $db->query($sql);

            if (count($ret) > 0) {
                $tokens       = [$ret[0]['reg_token']];
                $title        = $configs['services']['android_push']['tpl']['confirm_address']['title'];
                $message      = $configs['services']['android_push']['tpl']['confirm_address']['message'];
                $send_message = ['title' => $title, 'message' => $message];
                self::send_gcm_notify($tokens, $send_message);

            }
        }

        $sql  = "select email from sp_bind_email where uid = $uid";
        $info = $db->row($sql);
        if (isset($info['email'])) {
            $email_config                  = $configs['services']['email'];
            $real_email_content['subject'] = $email_config['info']['tpl']['confirm_address']['subject'];
            $real_email_content['body']    = $email_config['info']['tpl']['coupon_expired']['body'];
            $real_email_content['is_html'] = $email_config['info']['tpl']['confirm_address']['is_html'];
            $real_email_content['email']   = $info['email'];

            $sql  = "select w.luck_time, g.name from sp_win_record w join sp_goods g on w.`goods_id` = g.id where w.`nper_id` = $nper_id";
            $info = $db->row($sql);
            if (isset($info['luck_time']) && isset($info['name'])) {
                $real_email_content['body'] = str_replace(
                    "{{luck_date}}",
                    date("Y-m-d H:i:s", $info['luck_time'] / 1000),
                    $real_email_content['body']
                );
                $real_email_content['body'] = str_replace("{{good_name}}", $info['name'], $real_email_content['body']);
                EmailTask::send_email($real_email_content, $task);
            }

        }

    }

    public static function send_winning_bonus_push($nper_id)
    {
        global $db, $configs;

        $sql = "select o.uid, o.luck_status, t.reg_token from sp_order_list o join sp_reg_token t on o.uid = t.uid where nper_id = $nper_id and dealed='true'";

        $rows = $db->query($sql);
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $tokens = [];
                if ($row['luck_status'] == "true") {
                    $title        = $configs['services']['android_push']['tpl']['winning_bonus']['win']['title'];
                    $message      = $configs['services']['android_push']['tpl']['winning_bonus']['win']['message'];
                    $token        = [$row['reg_token']];
                    $send_message = ['title' => $title, 'message' => $message];
                    self::send_gcm_notify($token, $send_message);
                }
                else {
                    if(isset($row['reg_token'])) {
                        if (self::verify_limit($row['reg_token'])) {
                            $tokens[] = $row['reg_token'];
                        }
                    }
                }
            }

            $title        = $configs['services']['android_push']['tpl']['winning_bonus']['fail']['title'];
            $message      = $configs['services']['android_push']['tpl']['winning_bonus']['fail']['message'];
            $send_message = ['title' => $title, 'message' => $message];

            if(count($tokens) > 0 ) {
                self::send_gcm_notify($tokens, $send_message);
            }
        }

    }

    public static function verify_limit($token)
    {
        global $redis;
        $prefix    = "notice_count:" . date("Ymd", time()) . "#";
        $key       = $prefix . substr($token, 0, 8);
        $have_send = $redis->get($key);
        if ($have_send >= 1) {
            return false;
        }
        else {
            return true;
        }
    }

    public static function get_token_with_win_record_id($win_record_id)
    {
        global $db;
        $sql  = "select reg_token from sp_reg_token where uid = (select luck_uid  from sp_win_record where id = $win_record_id)";
        $info = $db->row($sql);
        if (isset($info['reg_token'])) {
            $ret[] = $info['reg_token'];

            return $ret;
        }
        else {
            return [];
        }
    }

    public static function get_token_with_show_order_ids($show_order_ids)
    {
        global $db;

        $sql        = <<<GETSQL
        select reg_token from sp_reg_token t join
(select distinct uid from `sp_order_list` o join
(select nper_id from sp_show_order  where id in ($show_order_ids)) n
on  o.nper_id = n.nper_id and o.`luck_status` = "false") u
on t.uid = u.uid
GETSQL;
        $reg_tokens = $db->query($sql);

        $ret = [];
        if (count($reg_tokens) > 0) {
            foreach ($reg_tokens as $v) {
                if (self::verify_limit($v['reg_token'])) {
                    $ret[] = $v['reg_token'];
                }
            }
        }

        return $ret;
    }

    public static function send_gcm_notify($tokens, $message)
    {

        global $configs;

        $key     = $configs['services']['android_push']['key'];
        $gcm_url = $configs['services']['android_push']['gcm_url'];

        //$tokens = [
        //    "fnoIgCJeBrA:APA91bFgVW0wdMyxKXNbaJMUB11BSmN964jdXaJqPaxbpfR8j8QhZklUl4eEwA-zjgKuiijXLCagj0t07z0Dwze2bDAjSqagmlNJZlnFMLhBICM1aiZHyWsW2W5wQ8mtDt5dh5PfQ_H_",
        //    "enUcQvCRH5Y:APA91bE_2aqdNYVQP6THG9iMfBAF3qmmSmax1zKvgLKGyX6uVUjzl6QPYSi27nU-aWtfXmLbeZyU0Rx7I8JY8i-r8usQ61OAe7kVCwUOJiY-kABVcvuIceVmTnl4_EWIj2IjsRM4JT7T",
        //];

        if (count($tokens) <= 0) {
            return;
        }

        self::cache_notice_times_per_day($tokens);

        $post_data = [
            'registration_ids' => $tokens,
            'data'             => $message,
        ];

        mdebug("notice content %s ", json_encode($post_data));

        $client = new \GuzzleHttp\Client(
            [
                'headers' => [
                    'Authorization' => "key=" . $key,
                ],
            ]
        );

        $response = $client->post($gcm_url, ['json' => $post_data]);

        mdebug("type %s response %s ", "gcm_push", $response->getBody()->getContents());

    }

    public static function cache_notice_times_per_day($tokens)
    {
        global $redis;
        if (count($tokens) > 0) {
            $prefix = "notice_count:" . date("Ymd", time()) . "#";
            foreach ($tokens as $k => $v) {
                $key = $prefix . substr($v, 0, 8);
                if (!$redis->exists($key)) {
                    $redis->set($key, 1);
                    $redis->expire($key, 3600 * 24);
                }
                else {
                    $redis->incr($key);
                }
                mdebug("%s value is %d", $key, $redis->get($key));
            }

        }
    }
}
