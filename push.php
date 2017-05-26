<?php

$arr = [

    //['type' => 'email', 'argv' => ['category' => "register", "country" => "malaysia", 'email' => "haozhongzhi@brotsoft.com"]],
    //['type' => 'email', 'argv' => ['category' => "payment", 'uid' => 1290, "order_id" => "1609270408223777" ]],
    //['type' => 'email', 'argv' => ['category' => "receipt", 'uid' => 1290, "win_record_id" => 1 ]],
    ['type' => 'notice', 'argv' => ['category' => "shipped", 'email' => "haozhongzhi@brotsoft.com", "win_record_id" => 1 ]],
];

foreach ($arr as $info) {

    $client = stream_socket_client('tcp://127.0.0.1:6161');
    fwrite($client, json_encode($info) . "\n");
    echo fread($client, 3000) . "\n";
    usleep(10000);
    fclose($client);
}

