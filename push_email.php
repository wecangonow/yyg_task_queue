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
    $to_emails                         = [];
    $counter                           = 0;
    $email_counter                     = 0;
    $email_task                        = [];
    $email_task['type']                = "email";
    $email_task['argv']['body']        = $email_tpl['body'];
    $email_task['argv']['country']     = $email_tpl['country'];
    $email_task['argv']['is_html']     = $email_tpl['is_html'];
    $email_task['argv']['subject']     = $email_tpl['subject'];
    $email_task['argv']['sender_info'] = $email_tpl['sender_info'];

    while (!feof($handle)) {

        $buffer = fgets($handle, 4096);
        $buffer = rtrim($buffer, "\n");
        if ($buffer != "") {
            $to_emails[] = $buffer;
            $email_counter++;
        }

        if (count($to_emails) == 50) {

            $counter++;
            echo "send 500 emails: " . $counter . "\n";
            $email_task['argv']['email_address'] = $to_emails;
            fwrite($client, json_encode($email_task) . "\n");
            minfo("server response: %s", fread($client, 100));

            $to_emails = [];

        }
    }

    if (count($to_emails) > 0) {

        $counter++;
        $email_task['argv']['email_address'] = $to_emails;
        fwrite($client, json_encode($email_task) . "\n");
        minfo("server response : %s", fread($client, 100));

    }

    fclose($handle);
}

fclose($client);

echo "totally send $email_counter emails to queue" . "\n";
