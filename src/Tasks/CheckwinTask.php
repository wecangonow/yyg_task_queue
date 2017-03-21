<?php
/**
 * Created by PhpStorm.
 * User: og
 * Date: 17/3/16
 * Time: 上午10:36
 */

namespace Yyg\Tasks;

class CheckWinTask implements TaskInterface
{
    public static function execute(array $task)
    {
        global $configs, $redis, $db;

        $npers = $task['argv']['npers'];

        $csv_header = ['期数', '商品价格', '中奖者ID', '中奖者积分', '是否机器人', '总购买人数(db|cache)', '符合条件人数', '机器人数量', '符合条件真人'];
        $csv_body   = [];

        foreach ($npers as $nper) {

            $nper_id      = $nper['id'];
            $winner_id    = $nper['winner_id'];
            $winner_score = $nper['winner_score'];
            $price        = $nper['price'];

            $total_user_num_in_db = $db->row(
                "select count(distinct uid) as total_user_num_in_db from sp_luck_num where nper_id = $nper_id"
            )['total_user_num_in_db'];

            $key = str_replace("{nid}", $nper_id, $configs['prize']['nper_prize_key_scheme']);

            $all_users_num       = $redis->executeRaw(['zcount', $key, "-inf", "+inf"]);
            $qualified_total     = $redis->executeRaw(['zcount', $key, $price, "+inf"]);
            $qualified_robot     = $redis->executeRaw(
                ['zcount', $key, $configs['prize']['rt_magic_prize'], $configs['prize']['rt_magic_prize']]
            );
            $qualified_real_user = $qualified_total - $qualified_robot;

            if($winner_score == $configs['prize']['rt_magic_prize']) {
                $is_robot = "是";
            } else {
                $is_robot = "否";
            }

            $row = $nper_id . "," . $price . "," . $winner_id . "," . $winner_score . "," . $is_robot . "," . $total_user_num_in_db . "|" . $all_users_num
                   . "," .  $qualified_total . "," . $qualified_robot . "," . $qualified_real_user . PHP_EOL;

            $csv_body[] = $row;


        }


        /**
         * 开始生成
         * 1. 首先将数组拆分成以逗号（注意需要英文）分割的字符串
         * 2. 然后加上每行的换行符号，这里建议直接使用PHP的预定义
         * 常量PHP_EOL
         * 3. 最后写入文件
         */


        $output = fopen($configs['file_path'] . date("Y-m-d_H-i-s", time()) . 'win_check.csv', 'a');

        $header = implode(',', $csv_header) . PHP_EOL;

        $content = "";
        foreach($csv_body as $row)
        {
            $content .= $row;
        }

        $csv = $header . $content;

        fwrite($output, $csv);

        fclose($output);
    }

}