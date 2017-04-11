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

        //判断该用户是否有资格抢该
        $nper_user_pay_key = str_replace("{nid}", $nper_id, $configs['bonus']['nper_user_pay_key']);

        $spend = $redis->executeRaw(['zscore', $nper_user_pay_key, $uid]);

        $ret = ['bonus_records' => [], 'is_win' => false, 'win_amount' => 0];

        if ($spend && $spend > 1) {

            $user_nper_get_bonus_record = str_replace(
                "{uid}",
                $uid,
                $configs['bonus']['user_every_nper_get_bonus_state']
            );

            $state = $redis->executeRaw(['zscore', $user_nper_get_bonus_record, $nper_id]);

            if ((int)$state == 0) {  // 没有抢

                //更新该用户对该期抢包得状态为1
                $redis->executeRaw(['zadd', $user_nper_get_bonus_record, 1, $nper_id]);

                $nper_bonus_total_key = str_replace(
                    "{nid}",
                    $nper_id,
                    $configs['bonus']['nper_bonus_total']
                );

                $remain = $redis->executeRaw(['get', $nper_bonus_total_key]);

                $num_user_can_get = rand(1, min($remain,floor($spend / 2)));
                $num_user_can_get = min($num_user_can_get, $configs['bonus']['max_bonus']);

                if ($configs['is_debug']) {
                    mdebug("before user %d purchase  nper %d | fund remain %d ", $uid, $nper_id, $remain);
                }

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


                if ($remain == 0) {

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
                    if ($num_user_can_get >= $remain) {
                        $decrby = $remain;
                    }
                    else {
                        $decrby = $num_user_can_get;
                    }

                    $remain_after_decr = $redis->executeRaw(['decrby', $nper_bonus_total_key, $decrby]);

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
                            $decrby,
                            'name',
                            $user_info['name']
                        ]
                    );

                    if ($configs['is_debug']) {
                        mdebug(
                            "user %d spend %d get %d bonus from  nper %d | still remain %d | user_info %s",
                            $uid,
                            $spend,
                            $decrby,
                            $nper_id,
                            $remain_after_decr,
                            json_encode($user_info)
                        );
                    }

                    $ret['win_amount'] = $decrby;
                    $ret['is_win'] = true;

                    if ($user_info['type'] != -1) {

                        //发送异步mysql update任务
                        $sql_task    = ['type' => 'bonusSync', 'argv' => ['uid' => $uid, 'amount' => $decrby]];
                        $sql_message = json_encode($sql_task);

                        $redis->lpush("message_queue", $sql_message);

                        if ($configs['is_debug']) {
                            mdebug("bonus sql sync task | %s", $sql_message);
                        }
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
        else {
            if ($configs['is_debug']) {
                mdebug("user %d not qualified get bonus of nper %d", $uid, $nper_id);
            }

            $info = sprintf("user %d not qualified get bonus of nper %d", $uid, $nper_id);
            $ret['info'] = $info;
            return json_encode($ret);
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
}
