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

        $nper_bonus_total_key = str_replace(
            "{nid}",
            $nper_id,
            $configs['bonus']['nper_bonus_total']
        );

        if ($redis->exists($nper_bonus_total_key)) {
            minfo("nper_id %d bonus has inited", $nper_id);

            return;
        }

        $nper_user_pay_key = str_replace("{nid}", $nper_id, $configs['bonus']['nper_user_pay_key']);

        $sql = "select sum(money) as spend_amount , uid from sp_order_list  where nper_id = $nper_id and dealed = 'true' and uid not in (select luck_uid from sp_nper_list where id = $nper_id )  group by uid";

        $nper_info = $db->query($sql);

        if (count($nper_info) > 0) {
            $goods_price = 0;
            foreach ($nper_info as $info) {

                $uid                        = $info['uid'];
                $spend_amount               = $info['spend_amount'];
                $user_nper_get_bonus_record = str_replace(
                    "{uid}",
                    $uid,
                    $configs['bonus']['user_every_nper_get_bonus_state']
                );

                //初始化该期每个用户的购买钱数  大于1 才可以抢
                $redis->executeRaw(['zadd', $nper_user_pay_key, $spend_amount, $uid]);

                //初始化用户的该期抢红包状态为0
                if ($spend_amount > 1) {
                    $redis->executeRaw(['zadd', $user_nper_get_bonus_record, 0, $nper_id]);
                }

                $goods_price += $spend_amount;

                if ($configs['is_debug']) {
                    mdebug(
                        "%s -- nper_id = %d user = %d spend %d ",
                        $nper_user_pay_key,
                        $nper_id,
                        $uid,
                        $spend_amount
                    );
                    if ($spend_amount > 1) {
                        mdebug(
                            "%s init user %d nper_id %d set bonus state to 0",
                            $user_nper_get_bonus_record,
                            $uid,
                            $nper_id
                        );
                    }
                }
            }

            // 向上取整
            $bonus_total = ceil($goods_price * $configs['bonus']['bonus_percent']);
            $redis->executeRaw(['set', $nper_bonus_total_key, (int)$bonus_total]);

            if ($configs['is_debug']) {
                mdebug("%s bonus total is %d", $nper_bonus_total_key, $bonus_total);
            }

        }

        ExecutionTime::End();

        minfo("%s::execute spend %s ", get_called_class(), ExecutionTime::ExportTime());
    }

}