<?php


namespace Yyg\Core;

class ExecutionTime
{
    private static $startTime;
    private static $endTime;

    public static function Start(){
        self::$startTime =  microtime(true);
    }

    public static function End(){
        self::$endTime =  microtime(true);
    }

    private static function runTime($end, $start) {
        return $end - $start;
    }

    public static function ExportTime(){
        return "This script used " . self::runTime(self::$endTime, self::$startTime) . "s \n";
    }
}