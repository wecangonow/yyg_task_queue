<?php
global $argv;

if (!isset($argv[1])) {
    exit("Usage: php push.php emails_list \n");
}

$emails_file = $argv['1'];

$handle = fopen($emails_file, "r") or die("Couldn't get handle");

$client = stream_socket_client('tcp://127.0.0.1:6161');

if ($handle) {
    $order_task['type']                = "prize";

    while (!feof($handle)) {

        $buffer = fgets($handle, 4096);
        $buffer = rtrim($buffer, "\n");
        echo $buffer . "\n";
        $order_task['argv']['order_id'] = $buffer;
        fwrite($client, json_encode($order_task) . "\n");

    }


    fclose($handle);
}

fclose($client);

