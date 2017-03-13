<?php

$win_records = [
    ['type' => 'syncprize', 'argv'=>['nper_id'=>1892]]
];
$fetch_winners = [
    ['type' => 'fetchwin', 'argv'=>['nper_id'=>1000, 'gid'=> 100]],
    ['type' => 'fetchwin', 'argv'=>['nper_id'=>1001, 'gid'=> 101]],
    ['type' => 'fetchwin', 'argv'=>['nper_id'=>1002, 'gid'=> 102]],
    ['type' => 'fetchwin', 'argv'=>['nper_id'=>1003, 'gid'=> 103]],
    ['type' => 'fetchwin', 'argv'=>['nper_id'=>1004, 'gid'=> 104]],
    ['type' => 'fetchwin', 'argv'=>['nper_id'=>1005, 'gid'=> 105]]
];


foreach($win_records as $record){
    $client = stream_socket_client('tcp://127.0.0.1:6161');
    fwrite($client, json_encode($record) . "\n");
    echo fread($client, 100) . "\n";
    fclose($client);
}

//foreach($fetch_winners as $record){
//    $client = stream_socket_client('tcp://127.0.0.1:6161');
//    fwrite($client, json_encode($record) . "\n");
//
//    $response = fread($client, 4096) ;
//    echo $response . "\n";
//    echo "winner_id is " . json_decode($response, true)['winner_id'] . "\n";
//    fclose($client);
//}

