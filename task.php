<?php

require_once __DIR__ . '/bootstrap.php';

use Workerman\Worker;
use Workerman\Lib\Timer;
use Clue\React\Redis\Factory;
use Clue\React\Redis\Client;

// task worker，使用Text协议
$task_worker = new Worker('Text://0.0.0.0:12345');
// task进程数可以根据需要多开一些
$task_worker->count     = 10;
$task_worker->name      = 'TaskWorker';


$task_worker->onWorkerStart = function($task_worker) {

   global $factory;
   $loop = Worker::getEventLoop();
   $factory = new Factory($loop);

   $time_interval = 5;


   Timer::add($time_interval, function() use($task_worker) {

      global $factory;
      $factory->createClient('localhost:6379')->then(function (Client $client) use ($task_worker) {

         $client->rpop('message_queue')->then(function ($message_queue) use ($task_worker) {
            if($message_queue != "") {
               echo "worker number " . $task_worker->id . ": send email to " . $message_queue . " " . date("Y-m-d H:i:s", time()) .  PHP_EOL;
               sleep(5);
            } else {
               echo "worker id -- " . $task_worker->id . " :task queue is empty " . date("Y-m-d H:i:s", time()) .  PHP_EOL;
            }
         });

         $client->end();
      });
   });
};


Worker::runAll();