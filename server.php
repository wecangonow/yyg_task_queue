<?php

require_once __DIR__ . '/bootstrap.php';

use Yyg\Configuration\ServerConfiguration;
use Oasis\Mlib\Logging\LocalFileHandler;
use Workerman\Worker;
use Clue\React\Redis\Factory;
use Clue\React\Redis\Client;


(new LocalFileHandler(ServerConfiguration::instance()->log_path))->install();

$worker = new Worker("text://0.0.0.0:6161");

$worker->onWorkerStart = function () {
    global $factory;
    $loop = Worker::getEventLoop();
    $factory = new Factory($loop);

};

$worker->onMessage = function($connection, $data) {
    global $factory;
    $factory->createClient('localhost:6379')->then(function (Client $client) use ($connection, $data) {

        $client->lpush("message_queue", $data);

        echo $data . "\n";
        $connection->send("get the task $data\n");



        $client->end();
    });
};


$worker->runAll();


