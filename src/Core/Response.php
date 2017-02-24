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
        }

        return $ret;
    }

    public static function emailResponse()
    {
        $email_count = count(self::$task['argv']['email_address']);
        $msg =  "server put $email_count email address to queue";
        return $msg;
    }
    
}