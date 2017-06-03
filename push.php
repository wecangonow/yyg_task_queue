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
    //['type' => 'notice', 'argv' => ['category' => "winning_bonus", 'nper_id' => 5555]],
    ['type' => 'upload_image', 'argv' =>
        [
            'img_id' => "10",
            'img_path' => "https://fb-s-b-a.akamaihd.net/h-ak-fbx/v/t1.0-1/c15.0.50.50/p50x50/10354686_10150004552801856_220367501106153455_n.jpg?oh=99f7a23b27b7b285107a17ae7a3003da&oe=59AF882F&__gda__=1504542980_0a1d507c7d984b09cff7f63a37f5a720",
            'img_type' => 'image/jpeg',
            'save_path' => 'data/img/170602/test1.jpg',
        ]
    ],
];

foreach ($arr as $info) {

    $client = stream_socket_client('tcp://127.0.0.1:6161');
    fwrite($client, json_encode($info) . "\n");
    echo fread($client, 3000) . "\n";
    usleep(10000);
    fclose($client);
}

