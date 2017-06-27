<?php
/**
 * Created by PhpStorm.
 * User: og
 * Date: 17/3/30
 * Time: 上午11:07
 */

namespace Yyg\Tasks;

use Yyg\Core\ExecutionTime;
class InitBonusTask implements TaskInterface
{

    public static function execute(array $task)
    {
        ExecutionTime::Start();

        global $db, $redis, $configs;
        $nper_id = $task['argv']['nper_id'];

        $robot_key = str_replace(
            "{nid}",
            $nper_id,
            $configs['bonus']['nper_robot_users']
        );


        if ($redis->exists($robot_key)) {
            minfo("nper_id %d bonus has inited", $nper_id);

            return;
        }


        $sql = "select sum(o.money) as spend_amount , o.uid, u.type from sp_order_list o join sp_users u on u.id = o.uid  where nper_id = $nper_id and dealed = 'true' and uid not in (select luck_uid from sp_nper_list where id = $nper_id )  group by uid";

        $nper_info = $db->query($sql);

        if($configs['is_debug']) {
            mdebug("debug nper info %s", json_encode($nper_info));
        }

        if (count($nper_info) > 0) {
            $nper_if_set_coupon_key = "if_set_coupon_state#" . $nper_id;

            $if_set_coupon = $redis->get($nper_if_set_coupon_key);

            foreach ($nper_info as $info) {

                $uid                        = $info['uid'];
                $type                       = $info['type'];
                $user_nper_get_bonus_record = str_replace(
                    "{uid}",
                    $uid,
                    $configs['bonus']['user_every_nper_get_bonus_state']
                );
                if($type == -1) {
                    self::addUidToRobotSet($nper_id, $uid);
                }

                if($if_set_coupon) {
                    //初始化用户的该期抢红包状态为0
                    $redis->executeRaw(['zadd', $user_nper_get_bonus_record, 0, $nper_id]);
                    self::initRobotFirstHunt($nper_id);
                } else {
                    $redis->executeRaw(['zadd', $user_nper_get_bonus_record, 1, $nper_id]);
                }

            }
        }

        ExecutionTime::End();

        minfo("%s::execute spend %s ", get_called_class(), ExecutionTime::ExportTime());
    }

    public static function initRobotFirstHunt($nper_id)
    {
        global $redis, $configs, $db;
        $sql = "select open_time from sp_nper_list where id = $nper_id";
        $open_time = $db->query($sql);
        $open_time = $open_time[0]['open_time'];
        $queue_key = $configs['robot_bonus_queue'];
        $first_time_gap = rand(500,3000) / 1000;
        $time = $open_time + $first_time_gap;
        $robot_bonus_task = ['type' => 'robotBonus', 'argv'=>['time' => $time, 'nper_id' => $nper_id]];
        $redis->lpush($queue_key, json_encode($robot_bonus_task));
        if ($configs['is_debug']) {
            mdebug("%s add first robot bonus task to queue |nper_id %d  open_time is %d sql is %s", $queue_key, $nper_id, $open_time, $sql);
        }
    }
    public static function addUidToRobotSet($nper_id, $uid)
    {
        global $redis, $configs;
        $key = str_replace(
            "{nid}",
            $nper_id,
            $configs['bonus']['nper_robot_users']
        );
        $redis->zadd($key,0,$uid);
        if ($configs['is_debug']) {
            mdebug("%s add uid %d to robot set ", $key, $uid);
        }
        
    }

}
