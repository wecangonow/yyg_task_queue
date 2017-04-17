<?php
/**
 * Created by PhpStorm.
 * User: og
 * Date: 17/4/12
 * Time: 下午2:22
 */

namespace Yyg\Tasks;

class BonusDataExportTask implements TaskInterface
{
    public static function execute(array $task)
    {
        global $configs, $redis, $db;

        //获取所有期的用户花费的key

        $nper_user_pay_key_pattern = str_replace("{nid}", "*", $configs['bonus']['nper_user_pay_key']);

        $nper_user_pay_keys = $redis->keys($nper_user_pay_key_pattern);

        file_put_contents($configs['file_path'] . "/nper_info.csv", "");
        file_put_contents($configs['file_path'] . "/bonus_info.csv", "");

        if (count($nper_user_pay_keys) > 0) {
            $nper_info_title = "期号,商品ID,商品名称,商品价格,中奖UID,中奖人昵称,中奖人花费,基金总额,基金剩余\n";
            file_put_contents($configs['file_path'] . "/nper_info.csv", $nper_info_title, FILE_APPEND);
            foreach ($nper_user_pay_keys as $key) {

                $nper_id = explode("#", $key)[1];

                $nper_bonus_total_key = str_replace(
                    "{nid}",
                    $nper_id,
                    $configs['bonus']['nper_bonus_total']
                );

                $user_spends = $redis->zrevrange($key, 0, -1, "withscores");
                $goods_price = 0;
                $data        = [];
                $index       = 0;
                foreach ($user_spends as $uid => $spend) {
                    $goods_price += $spend;
                    $bonus_state = self::ifUserQualified($nper_id, $uid);

                    $user_info = OpenBonusTask::getUserInfo($uid);
                    $user_type = $user_info['type'] == -1 ? "robot" : "normal";
                    $user_name = $user_info['name'];

                    $data[$index]['nper_id']     = $nper_id;
                    $data[$index]['uid']         = $uid;
                    $data[$index]['name']        = $user_name;
                    $data[$index]['type']        = $user_type;
                    $data[$index]['spend']       = $spend;
                    $data[$index]['bonus_state'] = $bonus_state;

                    $bonus_info = self::getUserBonusInfo($nper_id, $uid);
                    if ($bonus_info) {

                        $data[$index]['time']   = $bonus_info['time'];
                        $data[$index]['amount'] = $bonus_info['amount'];
                    }
                    else {
                        $data[$index]['time']   = "none";
                        $data[$index]['amount'] = "none";
                    }
                    $index++;
                }

                $contents = "期号,用户ID,名字,用户类型,用户花费,红包状态,抢到时间,抢到金额\n";
                // 向上取整
                $bonus_total = ceil($goods_price * $configs['bonus']['bonus_percent']);
                $bonus_left  = $redis->executeRaw(['get', $nper_bonus_total_key]);

                $sql = "select n.id, n.pid, g.name as goods_name , g.price, n.luck_uid , u.nick_name as user_name, (select sum(money) from sp_order_list where uid = n.luck_uid and nper_id = n.id and dealed = 'true') as total_spend
from
 sp_nper_list  n
 join sp_goods g on g.id = n.pid
 join sp_users u on n.luck_uid = u.id
 where n.id = $nper_id";

                $nper_info = $db->row($sql);
                $nper_string = implode(",", $nper_info) . "," . $bonus_total . "," . $bonus_left . "\n";
                file_put_contents($configs['file_path'] . "/nper_info.csv", $nper_string, FILE_APPEND);

                foreach ($data as $v) {
                    $contents .= implode(",", $v) . "\n";

                }
                file_put_contents($configs['file_path'] . "/bonus_info.csv", $contents);
            }

        }
        else {
            mdebug("type bonusDataExport : there are no data by now");
        }

    }

    public static function getUserBonusInfo($nper_id, $uid)
    {
        global $configs, $redis;

        $user_nper_get_bonus_record_detail = str_replace(
            "{nid}",
            $nper_id,
            $configs['bonus']['user_get_bonus_record_per_nper']
        );

        $user_nper_get_bonus_record_detail = str_replace(
            "{uid}",
            $uid,
            $user_nper_get_bonus_record_detail
        );

        $user_info = $redis->hgetall($user_nper_get_bonus_record_detail);

        if (!empty($user_info)) {
            return $user_info;
        }
        else {
            return false;
        }

    }

    // null 没有资格   0 还没有抢  1 是已经抢过

    public static function ifUserQualified($nper_id, $uid)
    {
        global $configs, $redis;

        $user_nper_get_bonus_record = str_replace(
            "{uid}",
            $uid,
            $configs['bonus']['user_every_nper_get_bonus_state']
        );

        $state = $redis->zscore($user_nper_get_bonus_record, $nper_id);

        if (is_null($state)) {
            $ret = "not qualified";
        }
        else if ($state == 0) {
            $ret = "not purchase";
        }
        else {
            $ret = "has purchased";
        }

        return $ret;
    }
    
}