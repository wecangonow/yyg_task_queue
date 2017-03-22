<?php
/**
 * Created by PhpStorm.
 * User: og
 * Date: 17/3/16
 * Time: 下午5:12
 */

namespace Yyg\Tasks;

// 处理机器人设置实际执行结果， 提供运营参考

class OpenflagTask implements TaskInterface
{
    public static function execute(array $task)
    {
        global $redis, $configs, $db;

        $flag = $task['argv']['flag'];
        $gid       = $task['argv']['gid'];
        $nper_id   = $task['argv']['nper_id'];
        $sum_times = $task['argv']['sum_times'];

        // flag 值为true 表示全黑
        // flag 值为false 表示触发警报  此时应该读取数据库查看该期的真实用户购买情况
        // flag 值为middle 表示半白
        $set_key  = str_replace("{gid}", $gid, $configs['prize']['goods_open_result']);
        $hash_key = str_replace("{nid}", $nper_id, $configs['prize']['goods_open_result_related_info']);

        $setting_sql    = "select `percentage`, `user_percentage` from `sp_rt_random_winning` where gid = $gid";
        $random_setting = $db->row($setting_sql);

        $percentage          = isset($random_setting['percentage']) ? $random_setting['percentage'] : "";
        $user_buy_percentage = isset($random_setting['user_percentage']) ? $random_setting['user_percentage'] : "";

        $trigger_info = [];
        if ($flag == 'false') {
            $trigger_info = $db->row(
                "select  sum(n.success_num) as total, u.id as uid, u.create_time as user_reg_time, n.`goods_name` from sp_order_list n join sp_users u on n.uid = u.id where n.nper_id = $nper_id  and n.dealed = 'true' and u.type = 1 group by n.uid order by total desc limit 1
"
            );

            mdebug(
                "nper_id %d gid %d trigger user buy percent alert | trigger_info %s",
                $nper_id,
                $gid,
                json_encode($trigger_info)
            );
        }

        $redis->executeRaw(['sadd', $set_key, $nper_id]);
        $redis->executeRaw(
            [
                'hmset',
                $hash_key,
                "flag",
                $flag,
                "sum_times",
                $sum_times,
                "percentage",
                $percentage,
                "user_buy_percentage",
                $user_buy_percentage,
                "trigger_info",
                json_encode($trigger_info),
            ]
        );

    }
    
}