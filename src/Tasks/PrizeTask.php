<?php

namespace Yyg\Tasks;

use Yyg\Core\ExecutionTime;

class PrizeTask implements TaskInterface
{
    public static function execute(array $task)
    {

        global $db, $configs;
        $order_id = $task['argv']['order_id'];

        // 根据订单号  获取用户 期数信息
        ExecutionTime::Start();

        $sql = "select l.paid, l.create_time, o.uid, u.score, u.type,  from `log_notify` l join `sp_order_list_parent` o  on o.order_id = l.order_id join `sp_users` u on o.uid = u.id  where l.order_id = $order_id and l.state = 'completed'";

        //$sql = "SELECT u.type, u.score, l.uid, l.nper_id, l.pay_time, l.money FROM `sp_order_list` l join `sp_users` u
        //        on l.uid = u.id WHERE l.pid = (select id from `sp_order_list_parent` where order_id = $order_id)";

        $ret = $db->row($sql);

        if(empty($ret)) {

        }

        ExecutionTime::End();

        echo ExecutionTime::ExportTime();

        extract($ret);

        //malaysia:user_period_consume:set#1000   马拉西亚 id为1000 的用户一段时间内消费记录  存储在redis 的set中
        //不是机器人
        if ($type != -1) {

            self::addUserPeriodConsumerToCache(
                str_replace("{uid}", $uid, $configs['prize']['period_consume_cache_key_scheme']),
                strtotime($create_time),
                $paid
            );

            $user_pay_life = $score;

            //从缓存得到user_pay_period

            //获取 用户的max_prize

            //$sql = "select max_prize, user_pay_life, user_pay_period, user_win_life from `sp_user_prize_statistics` where id = $uid";
            //
            //$statistics = $db->row($sql);
            //if (empty($statistics)) {
            //
            //}
            //else {
            //
            //}

        }
        else {
            $prize = $configs['prize']['rt_magic_prize'];
        }

        //$user_roi_expression = $configs['prize']['user_roi_expression'];
        //
        //
        //$user_roi = 0.9;
        //
        //$eval_str = str_replace('user_roi', $user_roi, $user_roi_expression);
        //
        //$ret = eval($eval_str);

        //minfo("order id is %s", $order_id);
        //
        //if($ret) {
        //    mdebug("eval result is true");
        //} else {
        //    mdebug("eval result is false");
        //}
        //mdebug("is_debug is %s", $configs['is_debug']);
        //mdebug("roi expression is %s", $user_roi_expression);

    }

    public static function getUserPayPeriod($key)
    {
        global $configs, $redis;

        $score = time() - 24 * 3600 * $configs['prize']['period_time'];

        $userPayPeriodSet = $redis->executeRaw([]);
    }

    public static function addUserPeriodConsumerToCache($key, $score, $value)
    {
        global $redis;

        $redis->executeRaw(['zadd', $key, $score, $value]);

    }
    
}