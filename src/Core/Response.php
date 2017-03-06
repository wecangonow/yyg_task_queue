<?php

namespace Yyg\Core;

class Response
{
    public static $task;

    public static function send(array $task)
    {
        self::$task = $task;
        $type = $task['type'];

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
        }

        return $ret;
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