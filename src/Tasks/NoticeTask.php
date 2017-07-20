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

            case "total":
                self::total_notice($task);
                $continue = false;
                break;
            case "register_coupon_expired":
                self::register_coupon_expired($task);
                $continue = false;
                break;
            case "activate_coupon":
                self::activate_coupon($task);
                $continue = false;
                break;
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

    public static function total_notice($task)
    {
        global $db, $redis;
        $real_send    = $task['argv']['real_send'];

        if($real_send) {
            $send_message = [
                'title'      => $task['argv']['title'],
                'message'    => $task['argv']['content'],
                'tickerText' => $task['argv']['ticker_text'],
            ];

            $msg_id = $task['argv']['msg_id'];
            $tokens = $task['argv']['tokens'];

            self::send_gcm_notify($tokens, $send_message, $msg_id);

            $success_key = "total_notice_success_count#" . $msg_id;
            $failed_key  = "total_notice_failed_count#" . $msg_id;

            $success_num = $redis->get($success_key);
            $failure_num = $redis->get($failed_key);

            $sql = "update sp_push_gcm set success_num = $success_num, fail_num = $failure_num where id = $msg_id";
            $db->query($sql);

        } else {

            $max_id = 0;

            $count = 1;
            for(;;) {

                $sql_count = "select id, reg_token from sp_reg_token where (`group` = 'android' or `group` is null) and id > $max_id limit  1000";

                $rows = $db->query($sql_count);

                $tokens = [];
                if(count($rows) > 0) {
                    foreach($rows as $v) {
                        if($v['id'] > $max_id) {
                            $max_id = $v['id'];
                        }
                        $tokens[] = $v['reg_token'];
                    }

                    $task['argv']['real_send'] = true;
                    $task['argv']['tokens'] = $tokens;

                    $redis->lpush("message_queue", json_encode($task));
                    unset($task['argv']['tokens']);
                    mdebug("total_noticetask for msg_id %d | %d round put %d tokens to slow queue max_id is %d | task_detail is %s", $task['argv']['msg_id'], $count, count($tokens), $max_id, json_encode($task));
                    $count++;

                } else {
                    break;
                }
            }

        }

    }

    public static function register_coupon_expired($task)
    {
        global $configs, $db;
        $token       = $task['argv']['reg_token'];
        $group       = $task['argv']['group'];
        $create_time = $task['argv']['create_time'];
        $category    = $task['argv']['category'];

        if ($token) {
            if (self::verify_limit($token)) {
                if ($group != "ios") {
                    $title        = $configs['services']['android_push']['tpl'][$category]['title'];
                    $message      = $configs['services']['android_push']['tpl'][$category]['message'];
                    $tokens       = [$token];
                    $send_message = ['title' => $title, 'message' => $message];
                    self::send_gcm_notify($tokens, $send_message);
                }
                else {
                    minfo("this is a iso push request but not available now, come soon");
                }

                $sql = "update sp_user_coupon set noticed = 1 where create_time = $create_time";
                $db->query($sql);
            }
        }

    }

    public static function activate_coupon($task)
    {
        global $configs;
        $token    = $task['argv']['reg_token'];
        $group    = $task['argv']['group'];
        $total    = $task['argv']['total'];
        $category = $task['argv']['category'];

        if ($token) {
            if (self::verify_limit($token)) {
                if ($group != "ios") {
                    $title        = $configs['services']['android_push']['tpl'][$category]['title'];
                    $message      = $configs['services']['android_push']['tpl'][$category]['message'];
                    $tokens       = [$token];
                    $title        = str_replace("{{total}}", $total, $title);
                    $send_message = ['title' => $title, 'message' => $message];
                    self::send_gcm_notify($tokens, $send_message);
                }
                else {
                    minfo("this is a iso push request but not available now, come soon");
                }

            }
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

                $sql        = "select g.name,  s.username from sp_show_order s join sp_goods g on g.id = s.goods_id where s.status = 1 order by s.id desc limit 4";
                $info       = $db->query($sql);
                $append_str = "\n";
                if (count($info) > 0) {
                    foreach ($info as $v) {
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
                    if (isset($row['reg_token'])) {
                        if (self::verify_limit($row['reg_token'])) {
                            $tokens[] = $row['reg_token'];
                        }
                    }
                }
            }

            $title        = $configs['services']['android_push']['tpl']['winning_bonus']['fail']['title'];
            $message      = $configs['services']['android_push']['tpl']['winning_bonus']['fail']['message'];
            $send_message = ['title' => $title, 'message' => $message];

            if (count($tokens) > 0) {
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
        if ($have_send >= 2) {
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

    public static function send_gcm_notify($tokens, $message, $msg_id = 0)
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

        if (!$msg_id) {
            self::cache_notice_times_per_day($tokens);
        }

        $post_data = [
            'registration_ids' => $tokens,
            'data'             => $message,
        ];

        //mdebug("notice content %s ", json_encode($post_data));

        $client = new \GuzzleHttp\Client(
            [
                'headers' => [
                    'Authorization' => "key=" . $key,
                ],
            ]
        );

        $response = $client->post($gcm_url, ['json' => $post_data]);

        $response_contents = $response->getBody()->getContents();


        if($msg_id) {
            $success_key = "total_notice_success_count#" . $msg_id;
            $failed_key  = "total_notice_failed_count#" . $msg_id;

            $res_arr = json_decode($response_contents, true);

            $success_num = $res_arr['success'];
            $failure_num = $res_arr['failure'];

            global $redis;

            $redis->incrby($success_key, $success_num);
            $redis->incrby($failed_key, $failure_num);

            //mdebug("type %s response %s ", "gcm_push", $response_contents);


        }

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
