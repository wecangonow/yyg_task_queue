<?php

require_once __DIR__ . '/bootstrap.php';

use Yyg\Client;
use Yyg\Configuration\ServerConfiguration;


//$conf = ServerConfiguration::instance()->swoole_server_info;
//
//$ip   = $conf['ip'];
//$port = $conf['port'];
//
//$mq = MqClient::getInstance(['ip' => $ip, 'port' => $port]);
//
//$mq->setQueue('sms');
//if (empty($argv[1]) || $argv[1] == 'push') {
//    $mq->push('asdfasdg');
//    //var_dump($r);
//}
//if (empty($argv[1]) || $argv[1] == 'pop') {
//    if (! empty($argv[2])) {
//        $mq->block(intval($argv[2]));
//    }
//    $r = $mq->pop();
//    var_dump($r);
//}
//if (empty($argv[1]) || $argv[1] == 'ack') {
//    $id = empty($argv[2]) ? $r['id'] : trim($argv[2]);
//    $r = $mq->ack($id);
//    var_dump($r);
//}

$client = new Client\TcpClient();
$client->connect();