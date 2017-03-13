<?php

namespace Yyg\Tasks;

use Yyg\Core\ExecutionTime;

class PrizeTask implements TaskInterface
{
    public static $max_prize;
    public static $user_pay_life;
    public static $user_win_life;
    public static $user_pay_period;
    public static $ratio;
    public static $user_roi;

    public static function execute(array $task)
    {
        ExecutionTime::Start();

        global $db, $configs;
        $order_id = trim($task['argv']['order_id']);
        if(!$order_id){
            return;
        }

        // 根据订单号  获取用户 期数信息

        $sql = "select l.paid, unix_timestamp(l.create_time), l.description as pay_type, o.uid, true as income  from `log_notify` l join `sp_order_list_parent` o  on o.order_id = l.order_id   where l.order_id = $order_id and l.state = 'completed'";


        $ret = $db->row($sql);

        if (empty($ret)) {
            $sql = "select uid, create_time, bus_type as pay_type, false as income from `sp_order_list_parent` where order_id = $order_id";
            $ret = $db->row($sql);
        }

        extract($ret);

        //malaysia:user_period_consume:set#1000   马拉西亚 id为1000 的用户一段时间内消费记录  存储在redis 的set中
        //不是机器人
        if (!self::isRobot($uid)) {



            //如果是充值和直接购买订单
            if ($income) {
                self::setUserPayPeriod(
                    $uid,
                    $create_time,
                    $paid
                );
            }

            if(in_array($pay_type, ['recharge', 'Recharge'])) {
                minfo("order_id = %s is recharge only update user_pay_period");
                return;
            }

            self::$user_pay_life = self::getUserPayPeriod($uid, "life");

            $is_first = self::firstOrder($uid);

            self::addUserToAlreadyConsumeSet($uid);


            if ($is_first) {

                self::$max_prize = self::$user_pay_life * $configs['prize']['loose_ratio'];

                self::$ratio = "loose_ratio";

            }
            else {

                self::$user_win_life = self::getUserWinLife($uid);

                if (self::$user_pay_life == 0) {
                    //一分钱没有充值的用户   -1
                    //$user_roi = -1;
                    self::$user_roi = -1;
                }
                else {
                    self::$user_roi = self::$user_win_life / self::$user_pay_life;
                }

                $user_roi_expression = $configs['prize']['user_roi_expression'];

                $eval_str = str_replace('user_roi', self::$user_roi, $user_roi_expression);

                $roi_check = eval($eval_str);

                if ($roi_check) {
                    self::$max_prize = self::$user_pay_life * $configs['prize']['zero_ratio'];
                    self::$ratio     = "zero_ratio";

                }
                else {

                    self::$user_pay_period = self::getUserPayPeriod($uid, "period");

                    if (self::$user_pay_period > $configs['prize']['period_money_top']) {
                        self::$max_prize = self::$user_pay_life * $configs['prize']['high_ratio'];
                        self::$ratio     = "high_ratio";
                    }
                    else {

                        self::$max_prize = self::$user_pay_life * $configs['prize']['low_ratio'];
                        self::$ratio     = "low_ratio";
                    }

                }

            }

            if ($configs['is_debug']) {
                mdebug(
                    "user %d : order_id = %d | user_pay_life = %s, user_pay_period = %s, max_prize = %s, user_win_life = %s, user_roi = %s, user_ratio = %s",
                    $uid, $order_id, self::$user_pay_life, self::$user_pay_period, self::$max_prize, self::$user_win_life, self::$user_roi, self::$ratio
                );
            }

        }
        else {
            mdebug("I am a robot");
            self::$max_prize = $configs['prize']['rt_magic_prize'];
        }

        $sql = "select nper_id from `sp_order_list` where pid in (select id from `sp_order_list_parent` where order_id = $order_id) and (index_end - index_start + 1) > 0 and bus_type = 'buy'";

        $npers = $db->query($sql);

        if(count($npers) > 0) {
            foreach($npers as $nper) {
                self::setNperPrize($nper['nper_id'], self::$max_prize, $uid);
                mdebug("nper_id = %d | max_prize = %d | uid = %d  ", $nper['nper_id'], self::$max_prize, $uid);
            }
        }

        ExecutionTime::End();

        minfo("%s::execute spend %s ", get_called_class(), ExecutionTime::ExportTime());

    }
    public static function setNperPrize($nper_id, $uid, $prize)
    {
        global $configs, $redis;
        $key = str_replace("{nid}", $nper_id, $configs['prize']['nper_prize_key_scheme']);
        $redis->executeRaw(['zadd', $key, $prize, $uid]);
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

    public static function isRobot($uid)
    {
        global $redis, $configs;

        $key = $configs['prize']['robot_set'];

        return $redis->executeRaw(['sismember', $key, $uid]);

    }

    //判断是否是用户首单
    public static function firstOrder($uid)
    {
        global $redis, $configs;

        $key = $configs['prize']['already_consume_user_key_scheme'];

        return !($redis->executeRaw(['sismember', $key, $uid]));

    }


    public static function addUserToAlreadyConsumeSet($uid)
    {
        global $redis, $configs;

        $key = $configs['prize']['already_consume_user_key_scheme'];

        $redis->executeRaw(['sadd', $key, $uid]);
    }

    public static function setUserPayPeriod($uid, $score, $value)
    {
        global $redis, $configs;

        $key = str_replace("{uid}", $uid, $configs['prize']['user_period_consume_key_scheme']);
        $redis->executeRaw(['zadd', $key, $score, $value]);

    }
    
    public static function getUserPayPeriod($uid, $range = "life")
    {
        global $configs, $redis;

        $key = str_replace("{uid}", $uid, $configs['prize']['user_period_consume_key_scheme']);

        if($range == "period") {
            $min_score = time() - 24 * 3600 * $configs['prize']['period_time'];
        } else {
            $min_score = "-inf";
        }

        $userPayPeriodSet = $redis->executeRaw(['zrevrangebyscore', $key, "+inf", $min_score]);

        return array_sum($userPayPeriodSet);
    }
}
