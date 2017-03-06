<?php

require_once __DIR__ . '/bootstrap.php';

use Workerman\Worker;
use Workerman\Lib\Timer;
use Clue\React\Redis\Factory;
use Clue\React\Redis\Client;
use Oasis\Mlib\Logging\LocalFileHandler;
use Yyg\Configuration\ServerConfiguration;

// task worker，使用Text协议
$task_worker = new Worker('Text://0.0.0.0:12345');
// task进程数可以根据需要多开一些
$task_worker->count = 15;
$task_worker->name  = 'TaskWorker';

$task_worker->onWorkerStart = function ($task_worker) {

    global $factory;
    $loop    = Worker::getEventLoop();
    $factory = new Factory($loop);

    //if($task_worker->id == 0)
    //    $time_interval = 5;
    //if($task_worker->id == 1)
    //    $time_interval = 7;
    //if($task_worker->id == 2)
    //    $time_interval = 9;
    //if($task_worker->id == 3)
    //    $time_interval = 10;
    //if($task_worker->id == 4)
    //    $time_interval = 15;

    $time_interval = 1;


    Timer::add(
        $time_interval,
        function () use ($task_worker) {

            global $factory, $task_result, $task_message;

            (new LocalFileHandler(ServerConfiguration::instance()->log_path))->install();

            $factory->createClient('localhost:6379')->then(
                function (Client $client) use ($task_worker, &$task_result, &$task_message) {

                    $client->rpop('message_queue')->then(
                        function ($message) use ($task_worker, &$task_result, &$task_message) {
                            if ($message != "") {
                                $task_message = $message;
                                $task_arr   = json_decode($message, true);
                                $task_type  = $task_arr['type'];
                                $task_class = "Yyg\\Tasks\\" . ucfirst($task_type) . "Task";
                                $task_result = $task_class::execute($task_arr);
                            }
                            else {
                                mdebug("worker id -- %d : task queue is empty", $task_worker->id);
                            }
                        }
                    );

                    if($task_result === false) {
                        $client->lpush("message_queue", $task_message);
                        minfo("Task  failed send back to queue again %s " , $task_message);
                    }
                    $client->end();
                }
            );
        }
    );
};

Worker::runAll();