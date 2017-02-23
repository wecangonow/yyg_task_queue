<?php

require_once __DIR__ . '/bootstrap.php';

use Workerman\Worker;
use Workerman\Lib\Timer;
use Clue\React\Redis\Factory;
use Clue\React\Redis\Client;

// task worker，使用Text协议
$task_worker = new Worker('Text://0.0.0.0:12345');
// task进程数可以根据需要多开一些
$task_worker->count     = 50;
$task_worker->name      = 'TaskWorker';

$task_worker->onWorkerStart = function($task) {

   global $factory;
   $loop = Worker::getEventLoop();
   $factory = new Factory($loop);

   $time_interval = 5;


   Timer::add($time_interval, function(){

      global $factory;
      $factory->createClient('localhost:6379')->then(function (Client $client) {

         $client->rpop('message_queue')->then(function ($message_queue) {
            if($message_queue != "") {
               echo "send email to " . $message_queue . " " . date("Y-m-d H:i:s", time()) .  PHP_EOL;
               sleep(10);
            } else {
               echo "task queue is empty " . date("Y-m-d H:i:s", time()) .  PHP_EOL;
            }
         });

         $client->end();
      });
   });
};


Worker::runAll();