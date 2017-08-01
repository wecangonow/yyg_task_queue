<?php
/**
 * Created by PhpStorm.
 * User: og
 * Date: 17/8/1
 * Time: 下午3:56
 */

namespace Yyg\Tasks;

use Yyg\Core\ExecutionTime;

class InitRobotFirstOrderTimeTask implements TaskInterface
{
    public static function execute(array $task)
    {

        ExecutionTime::Start();
        self::sync_new_nper_create_time();
        //机器人开启时
        global $db;
        // 获取所有商品的最后一单时间
        $time_sql = "select * from (select goods_id, pay_time from sp_order_list  where goods_id in ( select gid from sp_rt_regular where enable = 1 group by `gid` ) and success_num > 0 order by pay_time desc ) as tmp group by goods_id order by `goods_id`
";
        $times    = $db->query($time_sql);

        $ret = [];

        if (count($times) > 0) {
            foreach ($times as $time) {
                $exec_time = substr($time['pay_time'], 0, 10) + mt_rand(3600 * 3, 3600 * 6);
                $now       = time();

                if ($exec_time < $now) {
                    $exec_time = $now + mt_rand(3600, 3600 * 3);
                }
                $up_data['exec_time'] = $exec_time;

                $update_result = $db->update('sp_rt_regular')->cols($up_data)->where('gid=' . $time['goods_id'])->query(
                );
                if ($update_result) {
                    $ret[] = ['gid' => $time['goods_id'], 'exec_time' => date("Y-m-d H:i:s", $exec_time)];
                }
            }

        }

        minfo("Init robot first order time result: %s", json_encode($ret));

        ExecutionTime::End();
        minfo("%s::execute spend %s ", get_called_class(), ExecutionTime::ExportTime());
    }

    public static function sync_new_nper_create_time()
    {
        global $db;

        $sql = "select id, pid from sp_nper_list where status = 1";

        $npers = $db->query($sql);

        if(count($npers) > 0) {
            foreach($npers as $v) {
                $sql = "select open_time from sp_nper_list where status = 3 and pid = " . $v['pid'] . " order by id desc limit 1";

                $info = $db->row($sql);
                $open_time = $info['open_time'];
                $sql = "update sp_nper_list set create_time = " . $open_time . " where id = " . $v['id'];

                $db->query($sql);

                minfo("sync new nper create_time sql is %s", $sql);
            }
        }
    }

}