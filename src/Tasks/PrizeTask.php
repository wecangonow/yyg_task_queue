<?php

namespace Yyg\Tasks;

use Clue\React\Redis\Client;
use Yyg\Core\ExecutionTime;

class PrizeTask implements TaskInterface
{
    public static function execute(array $task)
    {

        global $db, $configs;
        $order_id = $task['argv']['order_id'];

        // 根据订单号  获取用户 期数信息
        ExecutionTime::Start();

        $sql = "SELECT u.type, u.score, l.uid, l.nper_id, l.pay_time, l.money FROM `sp_order_list` l join `sp_users` u on l.uid = u.id WHERE l.order_id=$order_id";

        $ret = $db->row($sql);

        ExecutionTime::End();

        echo ExecutionTime::ExportTime();

        if (empty($ret)) {
            echo "push task back to queue\n";
            return;
        }
        extract($ret);

        $type = 1; // 正式环境要删除
        //malaysia:user_period_consume:set#1000   马拉西亚 id为1000 的用户一段时间内消费记录  存储在redis 的set中
        //不是机器人
        if($type != -1 ) {
            self::addUserPeriodConsumerToCache(str_replace("{uid}", $uid, $configs['prize']['period_consume_cache_key_scheme']), round($pay_time / 1000), $money);
        } else {
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

    public static function addUserPeriodConsumerToCache($key, $score, $value)
    {
        global $redis;

        $redis->executeRaw(['zadd', $key, $score, $value]);

    }
    
}