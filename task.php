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
$task_worker->count = 5;
$task_worker->name  = 'TaskWorker';

$task_worker->onWorkerStart = function ($task_worker) {

    global $factory;
    $loop    = Worker::getEventLoop();
    $factory = new Factory($loop);

    $time_interval = 5;

    Timer::add(
        $time_interval,
        function () use ($task_worker) {

            global $factory;

            (new LocalFileHandler(ServerConfiguration::instance()->log_path))->install();

            $factory->createClient('localhost:6379')->then(
                function (Client $client) use ($task_worker) {

                    $client->rpop('message_queue')->then(
                        function ($message_queue) use ($task_worker, $client) {
                            if ($message_queue != "") {
                                $task_arr   = json_decode($message_queue, true);
                                $task_type  = $task_arr['type'];
                                $task_class = "Yyg\\Tasks\\" . ucfirst($task_type) . "Task";
                                $task_class::execute($task_arr, $client);
                            }
                            else {
                                mdebug("worker id -- %d : task queue is empty", $task_worker->id);
                            }
                        }
                    );

                    $client->end();
                }
            );
        }
    );
};

Worker::runAll();