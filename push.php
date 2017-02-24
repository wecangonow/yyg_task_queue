<?php
/*
 * push email task to queue
 */

require_once __DIR__ . '/bootstrap.php';

use Yyg\Configuration\ServerConfiguration;
use Oasis\Mlib\Logging\LocalFileHandler;

(new LocalFileHandler(ServerConfiguration::instance()->log_path))->install();

global $argv;

if (!isset($argv[1]) || !isset($argv[2]) || !isset($argv[3]) || !isset($argv[4])) {
    exit("Usage: php push.php emails_list email_tpl email_subject country\n");
}

$emails_file = $argv['1'];
$email_tpl   = $argv['2'];
$email_title = $argv['3'];
$country     = $argv['4'];

$handle = fopen($emails_file, "r") or die("Couldn't get handle");

$client = stream_socket_client('tcp://127.0.0.1:6161');

if ($handle) {
    $to_emails                         = [];
    $counter                           = 0;
    $email_counter                     = 0;
    $email_task                        = [];
    $email_task['type']                = "email";
    $email_task['argv']['body']   = file_get_contents($email_tpl);
    $email_task['argv']['subject'] = $email_title;

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
