<?php

$win_records = [
    ['type' => 'syncwin', 'argv'=>['nper_id'=>1000]]
];
$fetch_winners = [
    ['type' => 'fetchwin', 'argv'=>['nper_id'=>1000, 'gid'=> 100]],
    ['type' => 'fetchwin', 'argv'=>['nper_id'=>1001, 'gid'=> 101]],
    ['type' => 'fetchwin', 'argv'=>['nper_id'=>1002, 'gid'=> 102]],
    ['type' => 'fetchwin', 'argv'=>['nper_id'=>1003, 'gid'=> 103]],
    ['type' => 'fetchwin', 'argv'=>['nper_id'=>1004, 'gid'=> 104]],
    ['type' => 'fetchwin', 'argv'=>['nper_id'=>1005, 'gid'=> 105]]
];


$check_win = ['type' => 'checkwin', 'argv' => [
    'npers' => [

        ['id' => 5886, 'winner_id' => 1915174, 'winner_score' => 888888, 'price' => 57.00    ],
        ['id' => 5887, 'winner_id' => 1915185, 'winner_score' => 888888, 'price' => 57.00    ],
        ['id' => 5882, 'winner_id' => 1915181, 'winner_score' => 888888, 'price' => 384.00    ],
        ['id' => 5890, 'winner_id' => 1915160, 'winner_score' => 888888, 'price' => 57.00    ],
        ['id' => 5892, 'winner_id' => 1873379, 'winner_score' => 88.5, 'price' => 57.00    ],
        ['id' => 5888, 'winner_id' => 1915155, 'winner_score' => 888888, 'price' => 179.00    ],
        ['id' => 5895, 'winner_id' => 1915161, 'winner_score' => 888888, 'price' => 57.00    ],
        ['id' => 5896, 'winner_id' => 1915151, 'winner_score' => 888888, 'price' => 57.00    ],
        ['id' => 5894, 'winner_id' => 1915153, 'winner_score' => 888888, 'price' => 179.00    ],
        ['id' => 5899, 'winner_id' => 1915164, 'winner_score' => 888888, 'price' => 57.00    ],
        ['id' => 5900, 'winner_id' => 1915154, 'winner_score' => 888888, 'price' => 57.00    ],
        ['id' => 5904, 'winner_id' => 1915163, 'winner_score' => 888888, 'price' => 57.00    ],
        ['id' => 5905, 'winner_id' => 1915182, 'winner_score' => 888888, 'price' => 57.00    ],
        ['id' => 5903, 'winner_id' => 1915142, 'winner_score' => 888888, 'price' => 179.00    ],
        ['id' => 5874, 'winner_id' => 1915146, 'winner_score' => 888888, 'price' => 3449.00    ],
        ['id' => 5901, 'winner_id' => 1915176, 'winner_score' => 888888, 'price' => 384.00    ],
        ['id' => 5910, 'winner_id' => 1915181, 'winner_score' => 888888, 'price' => 57.00    ],
        ['id' => 5883, 'winner_id' => 1915185, 'winner_score' => 888888, 'price' => 1424.00    ],
        ['id' => 5911, 'winner_id' => 1915145, 'winner_score' => 888888, 'price' => 384.00    ],
        ['id' => 5916, 'winner_id' => 1915168, 'winner_score' => 888888, 'price' => 179.00    ],
        ['id' => 5917, 'winner_id' => 1915157, 'winner_score' => 888888, 'price' => 384.00    ],
        ['id' => 5919, 'winner_id' => 1915168, 'winner_score' => 888888, 'price' => 179.00    ],
        ['id' => 5922, 'winner_id' => 1915150, 'winner_score' => 888888, 'price' => 179.00    ]
    ]
]];



foreach($win_records as $record){
    $client = stream_socket_client('tcp://127.0.0.1:6161');
    fwrite($client, json_encode($record) . "\n");
    echo fread($client, 100) . "\n";
    fclose($client);
}

die;
$client = stream_socket_client('tcp://127.0.0.1:6161');
fwrite($client, json_encode($check_win) . "\n");
echo fread($client, 100) . "\n";
fclose($client);


//foreach($fetch_winners as $record){
//    $client = stream_socket_client('tcp://127.0.0.1:6161');
//    fwrite($client, json_encode($record) . "\n");
//
//    $response = fread($client, 4096) ;
//    echo $response . "\n";
//    echo "winner_id is " . json_decode($response, true)['winner_id'] . "\n";
//    fclose($client);
//}

