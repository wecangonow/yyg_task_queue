<?php
/**
 * Created by PhpStorm.
 * User: og
 * Date: 17/3/9
 * Time: 下午3:28
 */

namespace Yyg\Tasks;

use Yyg\Core\ExecutionTime;

class RobotBonusTask implements TaskInterface
{
    public static function execute(array $task)
    {
        global $redis, $configs;
        $nper_id = $task['argv']['nper_id'];
        $time = floor($task['argv']['time']);
        $now = time();
        if($time <= $now) {
            $key = str_replace(
                "{nid}",
                $nper_id,
                $configs['bonus']['nper_robot_users']
            );
            $uid = self::getRobotUid($key);

            if($uid) {
                OpenBonusTask::execute(['argv' => ['nper_id' => $nper_id, 'uid' => $uid]]);
                self::updateRobotBonusState($key, $uid);
                if(self::robotGetBonusFastPercent($key)) {
                    $total_robot = $redis->zcount($key, 0, 1);
                    $robot_left_num = $total_robot * 0.9;
                    $long_time_gap = 24 * 3600 / floor($robot_left_num);
                    $use_gap = rand(round($long_time_gap/2),$long_time_gap);
                    $time = time() + $use_gap;
                } else {
                    $time = time() + rand(1,5);
                }
                $data = ['type' => 'robotBonus', 'argv' => ['nper_id' => $nper_id, 'time' => $time]];
                minfo("put robot bonus task data %s", json_encode($data));
                $redis->lpush($configs['robot_bonus_queue'], json_encode($data));
            } else {
                minfo("robot uid runout for nper  %d", $nper_id);
            }
        } else {
            $redis->lpush($configs['robot_bonus_queue'], json_encode($task));
        }
        
    }

    public static function updateRobotBonusState($key, $uid)
    {
        global $redis;
        $redis->zadd($key, 1, $uid);

    }
    public static function getRobotUid($key)
    {
        global $redis;
        $count = $redis->zcount($key, 0, 0);
        if($count > 0) {
            $rand_index = rand(0, $count-1);
            $total_robot_uids = $redis->zrangebyscore($key, 0, 0);
            return $total_robot_uids[$rand_index];
        } else {
            return false;
        }
    }

    public static function robotGetBonusFastPercent($key)
    {
        global $redis;
        $have_get_robot_num = $redis->zcount($key, 1, 1);
        $total = $redis->zcount($key, 0, 1);
        //机器人比例大于30%以后拉长机器人抢的时间
        if($total != 0) {
            if($have_get_robot_num / $total > 0.1 ) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }
}
