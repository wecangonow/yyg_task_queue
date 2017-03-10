<?php

namespace Yyg\Tasks;

use Yyg\Core\ExecutionTime;

class PrizeTask implements TaskInterface
{
    public static $max_prize;
    public static $user_pay_life;
    public static $user_win_life;
    public static $user_pay_period;

    public static function execute(array $task)
    {
        ExecutionTime::Start();

        global $db, $configs, $redis;
        $order_id = $task['argv']['order_id'];

        $redis->lpush("message_queue", json_encode($task));
        // 根据订单号  获取用户 期数信息

        $sql = "select l.paid, l.create_time, o.uid, u.score, u.type, true as income  from `log_notify` l join `sp_order_list_parent` o  on o.order_id = l.order_id join `sp_users` u on o.uid = u.id  where l.order_id = $order_id and l.state = 'completed'";

        //$sql = "SELECT u.type, u.score, l.uid, l.nper_id, l.pay_time, l.money FROM `sp_order_list` l join `sp_users` u
        //        on l.uid = u.id WHERE l.pid = (select id from `sp_order_list_parent` where order_id = $order_id)";

        $ret = $db->row($sql);

        if (empty($ret)) {
            $sql = "select l.uid, l.create_time, u.score, u.type , false as income from `sp_order_list_parent` l join `sp_users` u on u.id = l.uid where l.order_id = $order_id";
            $ret = $db->row($sql);
        }

        extract($ret);

        $type = 1;
        //malaysia:user_period_consume:set#1000   马拉西亚 id为1000 的用户一段时间内消费记录  存储在redis 的set中
        //不是机器人
        if ($type != -1) {

            self::$user_pay_life = $score;

            self::setUserPayLife($uid, $score);

            $is_first = self::firstOrder($configs['prize']['already_consume_user_key_scheme'], $uid);

            //如果是充值和直接购买订单
            if ($income) {
                self::updateUserPeriodConsumer(
                    str_replace("{uid}", $uid, $configs['prize']['period_consume_cache_key_scheme']),
                    strtotime($create_time),
                    $paid
                );
                self::addUserToAlreadyConsumeSet($uid);
            }

            if($is_first) {

                self::$max_prize = self::$user_pay_life * $configs['prize']['loose_ratio'];

            } else {

                self::$user_win_life = self::getUserWinLife($uid);

                if(self::$user_pay_life == 0) {
                    //一分钱没有充值的用户   -1
                    $user_roi = -1;
                } else {
                    $user_roi = self::$user_win_life / self::$user_pay_life;
                }

                $user_roi_expression = $configs['prize']['user_roi_expression'];

                $eval_str = str_replace('user_roi', $user_roi, $user_roi_expression);

                $roi_check = eval($eval_str);

                if($roi_check) {

                    mdebug("eval result is true");
                    self::$max_prize = 0;

                } else {

                    self::$user_pay_period =  self::getUserPayPeriod($uid);

                    mdebug("eval result is false");

                }

                mdebug("roi is %d", $user_roi);
                mdebug("roi expression is %s", $user_roi_expression);

            }

        } else {
            self::$max_prize = $configs['prize']['rt_magic_prize'];
        }

        ExecutionTime::End();

        echo ExecutionTime::ExportTime();

    }

    public static function getUserWinLife($uid)
    {
        global $redis, $db, $configs;

        $key = str_replace("{uid}", $uid, $configs['prize']['user_life_win_key_scheme']);

        $exists = $redis->executeRaw(['exists', $key]);

        if (!$exists) {

            $sql = "select ifnull(sum(g.price), 0) as total from sp_win_record r join sp_goods g on r.goods_id = g.id   where luck_uid = $uid";

            $user_win_total = $db->row($sql)['total'];

            $redis->executeRaw(['set', $key, $user_win_total]);

            return $user_win_total;

        }
        else {
            return $redis->executeRaw(['get', $key]);
        }

    }

    //判断是否是用户首单
    public static function firstOrder($key, $uid)
    {
        global $redis;

        return $redis->executeRaw(['sismember', $key, $uid]);

    }

    public static function setUserPayLife($uid, $score)
    {
        global $redis, $configs;
        $key = str_replace("{uid}", $uid, $configs['prize']['user_life_pay_key_scheme']);
        $redis->executeRaw(['set', $key, $score]);

    }

    public static function getUserPayLife($uid)
    {
        global $redis, $configs;
        $key = str_replace("{uid}", $uid, $configs['prize']['user_life_pay_key_scheme']);

        return $redis->executeRaw(['get', $key]);
    }

    public static function getUserPayPeriod($uid)
    {
        global $configs, $redis;

        $key = str_replace("{uid}", $uid, $configs['prize']['user_period_consume_key_scheme']);

        $exists = $redis->executeRaw(['exists', $key]);

        if(!$exists) {
            return 0;
        }

        $score = time() - 24 * 3600 * $configs['prize']['period_time'];

        $userPayPeriodSet = $redis->executeRaw([]);
    }

    public static function addUserToAlreadyConsumeSet($uid)
    {

        $key = $configs['prize']['already_consume_user_key_scheme'];

        $redis->executeRaw(['sadd', $key, $uid]);
    }

    public static function updateUserPeriodConsumer($key, $score, $value)
    {
        global $redis;

        $redis->executeRaw(['zadd', $key, $score, $value]);

    }
    
}