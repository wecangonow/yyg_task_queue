<?php

namespace Yyg\Core;

use Yyg\Tasks\OpenBonusTask;
use Yyg\Common\Api;
class Response
{
    public static $task;

    public static function send(array $task)
    {
        self::$task = $task;
        $type       = strtolower($task['type']);

        switch ($type) {
            case "email":
                $ret = self::emailResponse();
                break;
            case "download":
                $ret = self::downloadResponse();
                break;
            case "prize":
                $ret = self::prizeResponse();
                break;
            case "fetchwin":
                $ret = self::fetchwinResponse();
                break;
            case "openbonus":
                $ret = self::openBonusResponse();
                break;
            case "bonusstate":
                $ret = self::bonusStateResponse();
                break;
            case "bonusstateall":
                $ret = self::bonusStateAllResponse();
                break;
            default:
                $ret = "got a message";
        }

        return $ret;
    }

    /**
     * 生成不重复的随机数
     *
     * @param  int $start  需要生成的数字开始范围
     * @param  int $end    结束范围
     * @param  int $length 需要生成的随机数个数
     *
     * @return array       生成的随机数
     */
    public static function get_rand_number($start = 1, $end = 10, $length = 4)
    {
        $connt = 0;
        $temp  = [];
        while ($connt < $length) {
            $temp[] = mt_rand($start, $end);
            $data   = array_unique($temp);
            $connt  = count($data);
        }

        sort($data);

        return $data;
    }

    public static function fetchwinResponse()
    {
        global $redis, $configs, $db;
        $nper_id = self::$task['argv']['nper_id'];
        $gid     = self::$task['argv']['gid'];

        $sql = "select uid, sum(money) as total from sp_order_list where nper_id = $nper_id and dealed = 'true' group by uid";

        $buy_records = $db->query($sql);

        $new_records = [];
        foreach($buy_records as $v) {
            $new_records[$v['uid']] = $v['total'];
        }

        $price = $db->row("select price from `sp_goods` where id = $gid")['price'];

        $key = str_replace("{nid}", $nper_id, $configs['prize']['nper_prize_key_scheme']);
        
        $users = $redis->executeRaw(['zrevrangebyscore', $key, "+inf", $price]);

        //$keys = array_keys($new_records);
        //$users = array_slice($keys, round(count($keys) / 2));

        $total_candidates = [];

        foreach($users as $uid) {
            $total = (int)$new_records[$uid];
            for($i = 0; $i < $total; $i++) {
                $total_candidates[] = $uid;
            }
        }

        shuffle($total_candidates);
        $winner_id = $total_candidates[array_rand($total_candidates, 1)];


        $score = $redis->executeRaw(['zscore', $key, $winner_id]);

        if ($configs['is_debug']) {
            mdebug("nper_id = %d before users %s after users %s", $nper_id, json_encode($users), json_encode($total_candidates));
        }

        minfo("fetch_win info nper_id = %d, winner_id = %d, score = %s, price = %s", $nper_id, $winner_id, $score, $price);

        return json_encode(['winner_id' => $winner_id, 'nper_id' => $nper_id, 'price' => $price]);
    }

    public static function emailResponse()
    {
        //$email_count = count(self::$task['argv']['email_address']);
        $msg         = "server put an email address to queue";

        return $msg;
    }
    
    public static function downloadResponse()
    {
        $msg = "server get an download url  to queue";

        return $msg;
    }

    public static function prizeResponse()
    {
        $order_id = self::$task['argv']['order_id'];
        $msg      = "server put prize task  to queue: order_id is $order_id";

        return $msg;
    }

    public static function openBonusResponse()
    {
        return OpenBonusTask::execute(self::$task);
    }

    public static function bonusStateResponse()
    {
        return Api::getUserNperBonusState(self::$task);
    }
    public static function bonusStateAllResponse()
    {
        return Api::getUserBonusState(self::$task);
    }
}
