<?php
/**
 * Created by PhpStorm.
 * User: og
 * Date: 17/6/8
 * Time: 上午11:09
 */

namespace Yyg\Tasks;

use Yyg\Core\ExecutionTime;

class CouponTask implements TaskInterface
{
    public static function execute(array $task)
    {
        ExecutionTime::Start();
        $category = $task['argv']['category'];

        switch ($category) {
            case "register":
                self::register($task);
                break;
            case "init_red_coupon":
                self::init_red_coupon($task);
                break;
        }

        ExecutionTime::End();
        minfo("%s::execute spend %s ", get_called_class(), ExecutionTime::ExportTime());
    }

    public static function init_red_coupon($task)
    {
        global $db, $redis;
        $pid     = $task['argv']['pid'];
        $nper_id = $task['argv']['nper_id'];

        $sql = "select coupon_data from sp_goods where id = $pid";
        $ret = $db->row($sql);

        $nper_if_set_coupon_key = "if_set_coupon_state#" . $nper_id;

        if (isset($ret['coupon_data']) && $ret['coupon_data'] != "") {

            $coupon_config = $ret['coupon_data'];
            //$coupon_config = '[{"id":"1","coupon_num":"3"},{"id":"2","coupon_num":"5"}]';
            $config_arr = json_decode($coupon_config, true);
            foreach ($config_arr as $v) {
                $coupon_id  = $v['id'];
                $coupon_num = $v['coupon_num'];
                $ids        = self::insert_coupon($coupon_id, $coupon_num);
                self::cache_coupon_info_per_nper($ids, $nper_id);
            }
            $redis->set($nper_if_set_coupon_key, 1);
        }
        else {
            $redis->set($nper_if_set_coupon_key, 0);
            merror("goods %d not have coupon config", $pid);
        }

    }

    public static function cache_coupon_info_per_nper($ids, $nper_id)
    {
        global $redis;

        $key = "red_coupon_ids#" . $nper_id;

        foreach ($ids as $id) {
            $redis->zadd($key, 0, $id);
            mdebug("nper %d add coupon id %d", $nper_id, $id);
        }
    }

    public static function insert_coupon($coupon_id, $num)
    {
        global $db;
        $sql       = "select * from sp_coupon_type where id = $coupon_id";
        $type_info = $db->row($sql);
        $ret       = [];
        for ($i = 0; $i < $num; $i++) {
            $insert_id = $db->insert("sp_user_coupon")->cols(
                [
                    'coupon_id'    => $coupon_id,
                    'title'        => $type_info['title'],
                    'category'     => "red_coupon",
                    'sub_title'    => $type_info['sub_title'],
                    'type'         => $type_info['type'],
                    'full_amount'  => $type_info['full_amount'],
                    'minus_amount' => $type_info['minus_amount'],
                    'expired_time' => time() + $type_info['expired_days'] * 24 * 3600,
                    'create_time'  => time(),

                ]
            )->query();

            $ret[] = $insert_id;
        }

        return $ret;
    }

    public static function register($task)
    {
        global $db, $configs;
        $uid      = $task['argv']['uid'];
        $category = $task['argv']['category'];

        $register_coupon_config = $configs['coupon']['register'];
        if (is_array($register_coupon_config)) {

            foreach ($register_coupon_config as $days => $coupon_ids) {
                if (is_array($coupon_ids)) {
                    foreach ($coupon_ids as $id => $count) {

                        $sql         = "select id, title,sub_title,type,full_amount,minus_amount, expired_days from sp_coupon_type where id = $id";
                        $type_info   = $db->row($sql);
                        $insert_data = [
                            'uid'          => $uid,
                            'coupon_id'    => $type_info['id'],
                            'title'        => $type_info['title'],
                            'category'     => $category,
                            'sub_title'    => $type_info['sub_title'],
                            'type'         => $type_info['type'],
                            'full_amount'  => $type_info['full_amount'],
                            'minus_amount' => $type_info['minus_amount'],
                        ];
                        $now         = time();
                        if ($days == 1) {
                            $insert_data['expired_time'] = $now + $type_info['expired_days'] * 24 * 3600;
                            $insert_data['create_time']  = $now;
                        }
                        else {
                            $add_day = $days - 1;
                            $create_time                 = strtotime("+$add_day days midnight");
                            $insert_data['expired_time'] = $create_time + $type_info['expired_days'] * 24 * 3600;
                            $insert_data['create_time']  = $create_time;
                            $insert_data['del_time']     = $create_time;
                        }

                        for ($i = 0; $i < $count; $i++) {
                            $insert_id = $db->insert("sp_user_coupon")->cols($insert_data)->query();
                            if ($insert_id) {
                                minfo(
                                    "day %d insert coupon create_time is %s insert_id is %d",
                                    $days,
                                    date("Y-m-d H:i:s", $insert_data['create_time']),
                                    $insert_id
                                );
                            }
                        }
                    }
                }
            }

        }
        else {
            mdebug("register return coupon config wrong check it");
        }

        return;
    }

    public static function addUidToRobotSet($nper_id, $uid)
    {
        global $redis, $configs;
        $key = str_replace(
            "{nid}",
            $nper_id,
            $configs['coupon']['nper_robot_users']
        );
        $redis->zadd($key, 0, $uid);
        if ($configs['is_debug']) {
            mdebug("%s add uid %d to coupon robot set ", $key, $uid);
        }

    }

}