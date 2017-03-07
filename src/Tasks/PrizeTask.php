<?php

namespace Yyg\Tasks;
use Clue\React\Redis\Client;

class PrizeTask implements TaskInterface
{
    public static function execute(array $task)
    {
        global $factory, $db, $configs;
        $order_id = $task['argv']['order_id'];

        $ret = $db->row("SELECT uid,nper_id FROM `sp_order_list` WHERE order_id=$order_id");

        print_r($ret);
        //$user_roi_expression = $configs['prize']['user_roi_expression'];
        //
        //
        //$user_roi = 0.9;
        //
        //$eval_str = str_replace('user_roi', $user_roi, $user_roi_expression);
        //
        //$ret = eval($eval_str);

        //minfo("order id is %s", $order_id);
        //
        //if($ret) {
        //    mdebug("eval result is true");
        //} else {
        //    mdebug("eval result is false");
        //}
        //mdebug("is_debug is %s", $configs['is_debug']);
        //mdebug("roi expression is %s", $user_roi_expression);
        //$factory->createClient('localhost:6379')->then(
        //    function (Client $client)  {
        //
        //        $client->rpop('message_queue')->then(
        //            function ($message_queue)  {
        //                echo json_decode($message_queue, true)['argv']['order_id'] . "\n";
        //            }
        //        );
        //
        //        $client->end();
        //    }
        //);

    }
    
}