<?php



$handle = fopen("large_email.csv", "r") or die("Couldn't get handle");

$client = stream_socket_client('tcp://127.0.0.1:6161');
if ($handle) {
    $to_emails = [];
    $counter = 1;

    while (!feof($handle)) {

        $buffer = fgets($handle, 4096);
        $buffer = rtrim($buffer,"\n");
        if($buffer != "") {
            $to_emails[] = $buffer;
        }

        if(count($to_emails) == 50) {

            $counter++;
            echo "send 500 emails: " . $counter . "\n";
            fwrite($client, json_encode($to_emails) . "\n");
            echo fread($client,100);

            $to_emails = [];

            usleep(1000);

        }
    }

    if(count($to_emails) > 0) {

        $counter++;
        //$client = stream_socket_client('tcp://127.0.0.1:6161');
        fwrite($client, json_encode($to_emails) . "\n");
        echo "send " . count($to_emails) . " emails: " . $counter . "\n";
        echo fread($client, 100);
        //fclose($client);

    }
    fclose($handle);
}

fclose($client);
//$loop->run();