<?php

$win_records = [
    ['type' => 'syncwin', 'argv'=>['uid' =>1,'nper_id'=>1000, 'price'=> 100]],
    ['type' => 'syncwin', 'argv'=>['uid' =>2,'nper_id'=>1001, 'price'=> 200]],
    ['type' => 'syncwin', 'argv'=>['uid' =>3,'nper_id'=>1002, 'price'=> 300]],
    ['type' => 'syncwin', 'argv'=>['uid' =>4,'nper_id'=>1003, 'price'=> 400]],
    ['type' => 'syncwin', 'argv'=>['uid' =>5,'nper_id'=>1004, 'price'=> 500]],
    ['type' => 'syncwin', 'argv'=>['uid' =>6,'nper_id'=>1005, 'price'=> 600]]
];
$fetch_winners = [
    ['type' => 'fetchwin', 'argv'=>['nper_id'=>1000, 'price'=> 100]],
    ['type' => 'fetchwin', 'argv'=>['nper_id'=>1001, 'price'=> 200]],
    ['type' => 'fetchwin', 'argv'=>['nper_id'=>1002, 'price'=> 300]],
    ['type' => 'fetchwin', 'argv'=>['nper_id'=>1003, 'price'=> 400]],
    ['type' => 'fetchwin', 'argv'=>['nper_id'=>1004, 'price'=> 500]],
    ['type' => 'fetchwin', 'argv'=>['nper_id'=>1005, 'price'=> 600]]
];


foreach($win_records as $record){
    $client = stream_socket_client('tcp://127.0.0.1:6161');
    fwrite($client, json_encode($record) . "\n");
    echo fread($client, 100) . "\n";
    fclose($client);
}

foreach($fetch_winners as $record){
    $client = stream_socket_client('tcp://127.0.0.1:6161');
    fwrite($client, json_encode($record) . "\n");
    echo fread($client, 100) . "\n";
    fclose($client);
}

