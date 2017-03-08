<?php

require_once __DIR__ . '/bootstrap.php';

use Workerman\Worker;
use Yyg\Core\Response;
use Workerman\Lib\Timer;
use Clue\React\Redis\Factory;
use Clue\React\Redis\Client;
use Oasis\Mlib\Logging\LocalFileHandler;

$task_worker        = new Worker('Text://0.0.0.0:12345');
$task_worker->count = 5;
$task_worker->name  = 'TaskWorker';

$task_worker->onWorkerStart = function ($task_worker) {

    global $factory, $db, $configs;
    require_once "config/config.php";

    $db      = new Workerman\MySQL\Connection(
        $configs['services']['mysql']['host'],
        $configs['services']['mysql']['port'],
        $configs['services']['mysql']['user'],
        $configs['services']['mysql']['password'],
        $configs['services']['mysql']['dbname']
    );
    $loop    = Worker::getEventLoop();
    $factory = new Factory($loop);

    $time_interval = $configs['timer_interval'];

    Timer::add(
        $time_interval,
        function () use ($task_worker) {

            global $factory, $configs;

            (new LocalFileHandler($configs['log_path']))->install();

            $factory->createClient($configs['services']['redis']['host'] . ':' . $configs['services']['redis']['port'])->then(
                function (Client $client) use ($task_worker) {

                    $client->rpop('message_queue')->then(
                        function ($message) use ($task_worker) {
                            if ($message != "") {
                                $task_arr   = json_decode($message, true);
                                $task_type  = $task_arr['type'];
                                $task_class = "Yyg\\Tasks\\" . ucfirst($task_type) . "Task";
                                $task_class::execute($task_arr);
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

$task_worker->onWorkerReload = function () {

    global $configs;
    require_once "config/config.php";

};

$task_worker->onMessage = function($connection, $data) {

    global $factory, $configs;

    (new LocalFileHandler($configs['log_path']))->install();

    $factory->createClient($configs['services']['redis']['host'] . ':' . $configs['services']['redis']['port'])->then(function (Client $client) use ($connection, $data) {

        $client->lpush("message_queue", $data);

        minfo("got task: %s", $data);
        $response = Response::send(json_decode($data,true));
        $connection->send($response);

        $client->end();
    });
};

Worker::runAll();