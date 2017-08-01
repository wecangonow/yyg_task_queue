<?php

require_once __DIR__ . '/bootstrap.php';

use Workerman\Worker;
use Yyg\Core\Response;
use Workerman\Lib\Timer;
use Oasis\Mlib\Logging\LocalFileHandler;


$task_worker        = new Worker('Text://0.0.0.0:6161');
$task_worker->count = 20;
$task_worker->name  = 'TaskWorker';

Worker::$logFile = '/tmp/workerman.log';

$task_worker->onWorkerStart = function ($task_worker) {

    global $db, $configs, $redis;
    require_once "config/config.php";

    $redis = new Predis\Client(
        [
            'scheme' => 'tcp',
            'host'   => $configs['services']['redis']['host'],
            'port'   => $configs['services']['redis']['port'],
        ]
    );
    $db    = new Workerman\MySQL\Connection(
        $configs['services']['mysql']['host'],
        $configs['services']['mysql']['port'],
        $configs['services']['mysql']['user'],
        $configs['services']['mysql']['password'],
        $configs['services']['mysql']['dbname']
    );

    $time_interval = $configs['timer_interval'];

    $worker_ids = [0, 4, 5, 6, 7, 9, 10,11,12,13,14,15,16,17,18,19];

    if($task_worker->id == 8) {
        Timer::add(1, ["\\Yyg\\Tasks\\AutoBuyCheckTask", "execute"], [[]]);
    }

    if (in_array($task_worker->id, $worker_ids)) {

        Timer::add(
            $time_interval,
            function () use ($task_worker) {

                global $configs, $redis;

                (new LocalFileHandler($configs['log_path']))->install();

                $message = $redis->rpop('message_queue');

                if ($message != "") {
                    $task_arr   = json_decode($message, true);
                    $task_type  = $task_arr['type'];
                    $task_class = "Yyg\\Tasks\\" . ucfirst($task_type) . "Task";
                    if (class_exists($task_class)) {
                        $task_class::execute($task_arr);
                    }

                }
                else {
                    if ($configs['is_debug']) {
                        mdebug("worker id -- %d : task queue is empty", $task_worker->id);
                    }
                }
            }
        );
    }
    if ($task_worker->id == 1) {

        Timer::add(
            0.5,
            function () use ($task_worker) {

                global $configs, $redis;

                (new LocalFileHandler($configs['log_path']))->install();

                $message = $redis->rpop($configs['robot_bonus_queue']);

                if ($message != "") {
                    $task_arr   = json_decode($message, true);
                    $task_type  = $task_arr['type'];
                    $task_class = "Yyg\\Tasks\\" . ucfirst($task_type) . "Task";
                    if (class_exists($task_class)) {
                        $task_class::execute($task_arr);
                    }
                }
                else {
                    if ($configs['is_debug']) {
                        mdebug("worker id -- %d : robot bonus queue is empty", $task_worker->id);
                    }
                }
            }
        );
    }

    if ($task_worker->id == 2 || $task_worker->id == 3) {

        Timer::add(
            5,
            function () use ($task_worker) {

                global $configs, $redis;

                (new LocalFileHandler($configs['log_path']))->install();

                $message = $redis->rpop('slow_queue');

                if ($message != "") {
                    $task_arr   = json_decode($message, true);
                    $task_type  = $task_arr['type'];
                    $task_class = "Yyg\\Tasks\\" . ucfirst($task_type) . "Task";
                    if (class_exists($task_class)) {
                        $task_class::execute($task_arr);
                    }
                }
                else {
                    if ($configs['is_debug']) {
                        mdebug("worker id -- %d : slow queue is empty", $task_worker->id);
                    }
                }
            }
        );
    }
};

$task_worker->onWorkerReload = function () {

    global $configs;
    require_once "config/config.php";

};

$task_worker->onMessage = function ($connection, $data) {

    global $configs, $redis;

    (new LocalFileHandler($configs['log_path']))->install();


    $type = json_decode($data, true)['type'];

    $not_push_queue_type = ['fetchwin', 'bonusStateAll', 'openBonus', 'bonusState'];

    $slow_task = ['notice', 'email'];

    if (!in_array($type, $not_push_queue_type)) {
        $info_hash_key = "message_hash:sets";
            $data_hash = md5($data);

            if($redis->sismember($info_hash_key, $data_hash)) {
            minfo("message %s repeat", $data);
            $connection->send("message repeat");
            return;

        } else {
            if(!$redis->exists($info_hash_key)) {
                $redis->sadd($info_hash_key, $data_hash);
                $redis->expire($info_hash_key, 3600);
            } else {
                $redis->sadd($info_hash_key, $data_hash);
            }
        }

        if (!in_array($type, $slow_task)) {
            $redis->lpush("message_queue", $data);
        }
        else {
            $redis->lpush("slow_queue", $data);
        }
    }

    minfo("got task: %s", $data);
    $response = Response::send(json_decode($data, true));
    $connection->send($response);
};

Worker::runAll();
