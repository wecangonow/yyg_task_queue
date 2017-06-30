<?php
/**
 * Created by PhpStorm.
 * User: og
 * Date: 17/3/30
 * Time: 下午2:38
 */

namespace Yyg\Tasks;

use Yyg\Core\ExecutionTime;

class OpenBonusTask implements TaskInterface
{
    public static function execute(array $task)
    {
        ExecutionTime::Start();

        global $configs, $redis;
        $nper_id = $task['argv']['nper_id'];
        $uid     = $task['argv']['uid'];

        $ret = ['bonus_records' => [], 'is_win' => false, 'win_amount' => 0];

        if (true) {

            $user_nper_get_bonus_record = str_replace(
                "{uid}",
                $uid,
                $configs['bonus']['user_every_nper_get_bonus_state']
            );

            $state = $redis->executeRaw(['zscore', $user_nper_get_bonus_record, $nper_id]);

            if ((int)$state == 0) {  // 没有抢

                //更新该用户对该期抢包得状态为1
                $redis->executeRaw(['zadd', $user_nper_get_bonus_record, 1, $nper_id]);

                //分配代金券id
                $coupon_id = self::get_coupon_id($nper_id);


                $open_time = time();

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

                $user_info = self::getUserInfo($uid);

                minfo("uid %d info is %s", $uid, json_encode($user_info));


                if ($coupon_id == 0) {

                    //该用户加入到该期抢包得失败集合中
                    $failed_set_key = str_replace(
                        "{nid}",
                        $nper_id,
                        $configs['bonus']['nper_get_bonus_failed_user_records']
                    );

                    $redis->executeRaw(['zadd', $failed_set_key, $open_time, $uid]);

                    //设置该用户该期的详细抢包记录

                    $redis->executeRaw(
                        [
                            'hmset',
                            $user_nper_get_bonus_record_detail,
                            'result',
                            "false",
                            'ip',
                            $user_info['ip'],
                            'type',
                            $user_info['type'],
                            'time',
                            $open_time,
                            'amount',
                            0,
                            'name',
                            $user_info['name']
                        ]
                    );

                    if ($configs['is_debug']) {
                        mdebug("user %d was added to failed key %s  ", $uid, $failed_set_key);
                    }

                    //返回该期所有中奖的人的信息
                    $ret['bonus_records'] = self::getSuccessPurchaseList($nper_id);

                    return json_encode($ret);

                }
                else {

                    $got = self::get_coupon_reduce_money($coupon_id);

                    //该用户加入到该期抢包得成功集合中
                    $success_set_key = str_replace(
                        "{nid}",
                        $nper_id,
                        $configs['bonus']['nper_get_bonus_success_user_records']
                    );

                    $redis->executeRaw(['zadd', $success_set_key, $open_time, $uid]);

                    //设置该用户该期的详细抢包记录

                    $redis->executeRaw(
                        [
                            'hmset',
                            $user_nper_get_bonus_record_detail,
                            'result',
                            "true",
                            'ip',
                            $user_info['ip'],
                            'type',
                            $user_info['type'],
                            'time',
                            $open_time,
                            'amount',
                            $got,
                            'name',
                            $user_info['name']
                        ]
                    );


                    $ret['win_amount'] = $got;
                    $ret['is_win'] = true;


                    //发送异步mysql update任务
                    $sql_task    = ['type' => 'bonusSync', 'argv' => ['uid' => $uid, 'coupon_id' => $coupon_id, 'nper_id' => $nper_id]];
                    $sql_message = json_encode($sql_task);

                    $redis->lpush("message_queue", $sql_message);

                    if ($configs['is_debug']) {
                        mdebug("bonus sql sync task | %s", $sql_message);
                    }

                    return json_encode($ret);
                }

            }
            else {
                if ($configs['is_debug']) {
                    mdebug("user %d has participate nper %d  bonus", $uid, $nper_id);
                }
                $info = sprintf("user %d has participate nper %d  bonus", $uid, $nper_id);
                $ret['info'] = $info;
                return json_encode($ret);
            }
        }

        ExecutionTime::End();

        minfo("%s::execute spend %s ", get_called_class(), ExecutionTime::ExportTime());
    }

    public static function getUserInfo($uid)
    {
        global $configs, $redis, $db;

        $user_info_key = str_replace("{uid}", $uid, $configs['bonus']['user_info']);

        $exists = $redis->executeRaw(['exists', $user_info_key]);

        if ($exists) {
            $user_info = $redis->hgetall($user_info_key);
        }
        else {
            $sql       = "select nick_name as name, reg_ip as ip, type from sp_users where id = $uid";
            $user_info = $db->row($sql);

            $redis->executeRaw(
                [
                    'hmset',
                    $user_info_key,
                    'name',
                    $user_info['name'],
                    'ip',
                    $user_info['ip'],
                    'type',
                    $user_info['type'],
                ]
            );
            $redis->executeRaw(['expire', $user_info_key, 3600 * 24 * 7]); //过期时间为一周
        }

        return $user_info;
    }

    public static function getSuccessPurchaseList($nper_id)
    {
        global $redis, $configs;

        $success_set_key = str_replace(
            "{nid}",
            $nper_id,
            $configs['bonus']['nper_get_bonus_success_user_records']
        );

        $uid_list = $redis->executeRaw(['zrevrange', $success_set_key, 0, 9]);

        $ret = [];

        foreach($uid_list as $uid) {
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

            $info['user_bonus_amount'] = $user_info['amount'];
            $info['user_ip'] = $user_info['ip'];
            $info['user_name'] = $user_info['name'];
            $info['user_open_time'] = $user_info['time'];
            $ret[] = $info;

        }

        return $ret;

    }

    public static function get_coupon_reduce_money($id)
    {
        global $db;

        $sql = "select minus_amount from sp_user_coupon where id = $id";

        $ret = $db->row($sql);

        return $ret['minus_amount'];

    }

    //返回coupon的id
    public static function get_coupon_id($nper_id)
    {
        global $redis;

        $key = "red_coupon_ids#" . $nper_id;

        $ids = $redis->executeRaw(['zrangebyscore', $key, 0, 0]);

        $count = count($ids);
        if($count > 0) {
            $rand = rand(0, $count - 1);
            $ret = $ids[$rand];
            $redis->executeRaw(['zadd', $key, 1, $ret]);
            return $ret;
        } else {
            return 0;
        }
    }

}
