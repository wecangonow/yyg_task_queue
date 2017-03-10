<?php
/*
 * push email task to queue
 */

require_once __DIR__ . '/bootstrap.php';

require_once "email.tpl.php";

use Yyg\Configuration\ServerConfiguration;
use Oasis\Mlib\Logging\LocalFileHandler;

(new LocalFileHandler(ServerConfiguration::instance()->log_path))->install();


global $argv;

if (!isset($argv[1])) {
    exit("Usage: php push.php emails_list \n");
}

$emails_file = $argv['1'];

$handle = fopen($emails_file, "r") or die("Couldn't get handle");

$client = stream_socket_client('tcp://127.0.0.1:6161');

if ($handle) {
    $order_task['type']                = "syncprize";

    while (!feof($handle)) {


        $buffer = fgets($handle, 4096);
        $buffer = rtrim($buffer, "\n");
        echo $buffer . "\n";
        $order_task['argv']['order_id'] = $buffer;
        fwrite($client, json_encode($order_task) . "\n");
        minfo("server response: %s", fread($client, 100));

        break;
    }


    fclose($handle);
}

fclose($client);

