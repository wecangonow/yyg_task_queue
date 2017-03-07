<?php

require_once __DIR__ . '/bootstrap.php';

use Yyg\Core\Response;
use Oasis\Mlib\Logging\LocalFileHandler;
use Workerman\Worker;
use Clue\React\Redis\Factory;
use Clue\React\Redis\Client;



$worker = new Worker("text://0.0.0.0:6161");

$worker->onWorkerStart = function () {

    global $factory;
    $loop = Worker::getEventLoop();
    $factory = new Factory($loop);

};

$worker->onMessage = function($connection, $data) {

    global $factory, $configs;
    require_once "config/config.php";

    (new LocalFileHandler($configs['log_path']))->install();

    $factory->createClient($configs['services']['redis']['host'] . ':' . $configs['services']['redis']['port'])->then(function (Client $client) use ($connection, $data) {

        $client->lpush("message_queue", $data);

        minfo("got task: %s", $data);
        $response = Response::send(json_decode($data,true));
        $connection->send($response);

        $client->end();
    });
};


$worker->runAll();


