<?php

$arr = [

    //['type' => 'email', 'argv' => ['category' => "register", "country" => "malaysia", 'email' => "haozhongzhi@brotsoft.com"]],
    //['type' => 'email', 'argv' => ['category' => "payment", 'uid' => 1290, "order_id" => "1609270408223777" ]],
    //['type' => 'email', 'argv' => ['category' => "receipt", 'uid' => 1290, "win_record_id" => 1 ]],
    //['type' => 'notice', 'argv' => ['category' => "shipped",  "win_record_id" => 2339 ]],
    //['type' => 'email', 'argv' => ['category' => "shipped",  "win_record_id" => 3774 ]],
    //['type' => 'email', 'argv' => ['category' => "show_order", 'show_order_ids' => "538,547"]],
    //['type' => 'notice', 'argv' => ['category' => "show_participate", 'show_order_ids' => "538,547"]],
    //['type' => 'notice', 'argv' => ['category' => "shipped", 'win_record_id' => 538]],
    ['type' => 'notice', 'argv' => ['category' => "winning_bonus", 'nper_id' => 5555]],
];

foreach ($arr as $info) {

    $client = stream_socket_client('tcp://127.0.0.1:6161');
    fwrite($client, json_encode($info) . "\n");
    echo fread($client, 3000) . "\n";
    usleep(10000);
    fclose($client);
}

