<?php

namespace Yyg\Core;

class Response
{
    public static $task;

    public static function send(array $task)
    {
        self::$task = $task;
        $type = strtolower($task['type']);

        switch($type) {
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
            default:
                $ret = "got a message";
        }

        return $ret;
    }

    /** 
     * 生成不重复的随机数 
     * @param  int $start  需要生成的数字开始范围 
     * @param  int $end    结束范围 
     * @param  int $length 需要生成的随机数个数 
     * @return array       生成的随机数 
     */  
    public static function get_rand_number($start=1,$end=10,$length=4)
    {  
        $connt=0;  
        $temp=array();  
        while($connt<$length){  
            $temp[]=mt_rand($start,$end);  
            $data=array_unique($temp);  
            $connt=count($data);  
        }  

        sort($data);
        return $data;  
    }  


    public static function fetchwinResponse()
    {
        global $redis, $configs, $db;
        $nper_id = self::$task['argv']['nper_id'];
        $gid     = self::$task['argv']['gid'];

        $price = $db->row("select price from `sp_goods` where id = $gid")['price'];

        //mdebug("goods %d price is %s", $gid, json_encode($price));

        $key = str_replace("{nid}", $nper_id, $configs['prize']['nper_prize_key_scheme']);
        
        //$nums = self::get_rand_number(1, 2 * $price, $price);
        //$uids = self::get_rand_number($price, 4 * $price, $price);
        //
        //foreach($nums as $k => $num){
        //    $redis->executeRaw(['zadd', $key, $num, $uids[$k]]);
        //    mdebug("add uid %d score %d to sorted set | key = %s", $uids[$k], $num, $key);
        //}

        
        $users = $redis->executeRaw(['zrevrangebyscore', $key, "+inf", $price]);

        $winner_id = $users[array_rand($users, 1)];

        $score = $redis->executeRaw(['zscore', $key, $winner_id]);

        mdebug("nper_id = %d, winner_id = %d, score = %d, price = %d", $nper_id, $winner_id, $score, $price);

        return json_encode(['winner_id' => $winner_id, 'nper_id' => $nper_id, 'price' => $price]);
    }

    public static function emailResponse()
    {
        $email_count = count(self::$task['argv']['email_address']);
        $msg =  "server put $email_count email address to queue";
        return $msg;
    }
    
    public static function downloadResponse()
    {
        $msg =  "server get an download url  to queue";
        return $msg;
    }

    public static function prizeResponse()
    {
        $order_id = self::$task['argv']['order_id'];
        $msg =  "server put prize task  to queue: order_id is $order_id";
        return $msg;
    }
}
