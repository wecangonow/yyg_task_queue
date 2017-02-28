<?php
/**
 * Created by PhpStorm.
 * User: og
 * Date: 17/2/24
 * Time: 下午4:11
 */

namespace Yyg\Tasks;
require_once PROJECT_DIR . '/vendor/phpmailer/phpmailer/PHPMailerAutoload.php';
use Yyg\Configuration\ServerConfiguration;
use Clue\React\Redis\Client;

class EmailTask implements   TaskInterface
{
    public static function execute(array $task, Client $res_client)
    {
        $ses_info = ServerConfiguration::instance()->email;

        $country = $task['argv']['country'];
        $mail = new \PHPMailer();

        $mail->isSMTP();

        $mail->Host     = $ses_info['host'];
        $mail->Port     = $ses_info['port'];
        $mail->SMTPAuth = $ses_info['auth'];
        $mail->Username = $ses_info['username'];
        $mail->Password = $ses_info['password'];
        //$mail->SMTPDebug = 2;
        $mail->setFrom($ses_info['info'][$country]['sender'], $task['argv']['sender_info']);
        $mail->addReplyTo($ses_info['info'][$country]['receiver']);
        $mail->CharSet = 'UTF-8';
        $mail->IsHTML($task['argv']['is_html']);
        $mail->Subject = $task['argv']['subject'];
        $mail->Body    = $task['argv']['body'];
        $emails = $task['argv']['email_address'];

        foreach ($emails as $email) {
            $mail->addBCC($email);

        }

        if (!$mail->send()) {
            merror("Mailer Error: %s " , $mail->ErrorInfo);
        }
        else {
            minfo("Task type %s  successfully to %s " , $task['type'], implode("|",$emails));
        }

    }
}