<?php

namespace Yyg\Tasks;

class NoticeTask implements TaskInterface
{
    public static function execute(array $task)
    {
        global $configs;

        $category = $task['argv']['category'];

        $tokens   = [];
        $continue = true;
        switch ($category) {

            case "nocheckin":
                $tokens = $task['argv']['tokens'];
                foreach ($tokens as $k => $v) {
                    if(!self::verify_limit($v)) {
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
                self::send_winning_bonus_push($nper_id, $task);
                $continue = false;
                break;
        }

        if ($continue) {
            $title        = $configs['services']['android_push']['tpl'][$category]['title'];
            $message      = $configs['services']['android_push']['tpl'][$category]['message'];
            $send_message = ['title' => $title, 'message' => $message];

            self::send_gcm_notify($tokens, $send_message, $task);
        }
    }

    public static function send_winning_bonus_push($nper_id, $task)
    {
        global $db, $configs;

        $sql  = "select o.uid, o.luck_status, t.reg_token from sp_order_list o join sp_reg_token t on o.uid = t.uid where nper_id = $nper_id and dealed='true'";
    
        $rows = $db->query($sql);
        if (count($rows) > 0) {
            foreach($rows as $row) {
                if($row['luck_status'] == "true") {
                    $title        = $configs['services']['android_push']['tpl']['winning_bonus']['win']['title'];
                    $message      = $configs['services']['android_push']['tpl']['winning_bonus']['win']['message'];
                    $token = [$row['reg_token']];
                    $send_message = ['title' => $title, 'message' => $message];
                    self::send_gcm_notify($token, $send_message, $task);
                } else {
                    if(self::verify_limit($row['reg_toekn'])) {
                        $tokens[] = $row['reg_token'];
                    }
                }
            }

            $title        = $configs['services']['android_push']['tpl']['winning_bonus']['fail']['title'];
            $message      = $configs['services']['android_push']['tpl']['winning_bonus']['fail']['message'];
            $send_message = ['title' => $title, 'message' => $message];

            self::send_gcm_notify($tokens, $send_message, $task);
        }
    }

    public static function verify_limit($token)
    {
        global $redis;
        $prefix = "notice_count:" . date("Ymd",time()) . "#";
        $key = $prefix . substr($token, 0, 8);
        $have_send = $redis->get($key);
        if($have_send >= 1){
            return false;
        } else {
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
                if(self::verify_limit($v['reg_token'])) {
                    $ret[] = $v['reg_token'];
                }
            }
        }

        return $ret;
    }

    public static function send_gcm_notify($tokens, $message, $task)
    {

        global $configs;

        $key     = $configs['services']['android_push']['key'];
        $gcm_url = $configs['services']['android_push']['gcm_url'];

        $tokens = [
            "fnoIgCJeBrA:APA91bFgVW0wdMyxKXNbaJMUB11BSmN964jdXaJqPaxbpfR8j8QhZklUl4eEwA-zjgKuiijXLCagj0t07z0Dwze2bDAjSqagmlNJZlnFMLhBICM1aiZHyWsW2W5wQ8mtDt5dh5PfQ_H_",
            "enUcQvCRH5Y:APA91bE_2aqdNYVQP6THG9iMfBAF3qmmSmax1zKvgLKGyX6uVUjzl6QPYSi27nU-aWtfXmLbeZyU0Rx7I8JY8i-r8usQ61OAe7kVCwUOJiY-kABVcvuIceVmTnl4_EWIj2IjsRM4JT7T",
        ];

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
        global $redis, $configs;
        if(count($tokens) > 0) {
            $prefix = "notice_count:" . date("Ymd",time()) . "#";
            foreach($tokens as $k => $v) {
                $key = $prefix . substr($v, 0, 8);
                if(!$redis->exists($key)){
                    $redis->set($key,1);
                    $redis->expire($key, 3600 * 24);
                } else {
                    $redis->incr($key);
                }
                mdebug("%s value is %d", $key, $redis->get($key));
            }

        }
    }
}
