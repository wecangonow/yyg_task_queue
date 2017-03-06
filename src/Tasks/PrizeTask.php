<?php
/**
 * Created by PhpStorm.
 * User: og
 * Date: 17/3/6
 * Time: 下午5:12
 */

namespace Yyg\Tasks;
use Clue\React\Redis\Client;

class PrizeTask implements TaskInterface
{
    public static function execute(array $task)
    {
        global $factory;

        $factory->createClient('localhost:6379')->then(
            function (Client $client2)  {

                $client2->rpop('message_queue')->then(
                    function ($message_queue)  {
                        echo json_decode($message_queue, true)['argv']['order_id'] . "\n";
                    }
                );

                $client2->end();
            }
        );
        return true;

    }
    
}