<?php

require_once __DIR__ . '/bootstrap.php';

use Yyg\Configuration\ServerConfiguration;
use Oasis\Mlib\Logging\LocalFileHandler;
use Workerman\Connection\AsyncTcpConnection;
use Workerman\Worker;

(new LocalFileHandler(ServerConfiguration::instance()->log_path))->install();

$worker = new Worker("text://0.0.0.0:6161");

$worker->onWorkerStart = function () {

    global $pull;
    $loop = Worker::getEventLoop();
    $context = new React\ZMQ\Context($loop);
    $pull = $context->getSocket(ZMQ::SOCKET_PULL);
    $pull->bind("tcp://127.0.0.1:5555");

    $pull->on("error", function($e){
       var_dump($e->getMessage());
    });

    $pull->on("message", function($msg){

        // 与远程task服务建立异步链接，ip为远程task服务的ip，如果是本机就是127.0.0.1，如果是集群就是lvs的ip
        $task_connection = new AsyncTcpConnection('Text://127.0.0.1:12345');
        // 发送数据
        $task_connection->send($msg);
        // 异步获得结果
        $task_connection->onMessage = function($task_connection, $task_result)
        {
            // 结果
            var_dump($task_result);
            // 获得结果后记得关闭异步链接
            $task_connection->close();
        };
        // 执行异步链接
        $task_connection->connect();

    });
};

$worker->onMessage = function($conn, $data) {
    var_dump($data);
    $conn->send("Hello world");
};

$worker->runAll();
