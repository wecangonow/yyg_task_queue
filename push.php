<?php

require "vendor/autoload.php";

$loop = React\EventLoop\Factory::create();

$context = new React\ZMQ\Context($loop);

$push = $context->getSocket(ZMQ::SOCKET_PUSH);

$push->connect('tcp://127.0.0.1:5555');

$handle = fopen("large_email.csv", "r") or die("Couldn't get handle");

if ($handle) {
    $to_emails = [];
    $counter = 0;

    while (!feof($handle)) {

        $buffer = fgets($handle, 4096);
        $buffer = rtrim($buffer,"\n");
        if($buffer != "") {
            $to_emails[] = $buffer;
        }

        if(count($to_emails) == 50) {

            $counter++;
            echo "send 50 emails: " . $counter . "\n";
            $push->send(json_encode($to_emails));

            $to_emails = [];

            usleep(500);

        }
    }

    if(count($to_emails) > 0) {

        var_dump($to_emails);
        $counter++;
        $push->send(json_encode($to_emails));
        echo "send " . count($to_emails) . " emails: " . $counter . "\n";

    }
    fclose($handle);
}

$loop->run();