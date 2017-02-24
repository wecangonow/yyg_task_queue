<?php
/**
 * Created by PhpStorm.
 * User: og
 * Date: 17/2/24
 * Time: 下午4:11
 */

namespace Yyg\Core;
require PROJECT_DIR . '/vendor/phpmailer/phpmailer/PHPMailerAutoload.php';
use Yyg\Configuration\ServerConfiguration;

class EmailTask implements   TaskInterface
{
    public static function execute(array $task)
    {
        $ses_info = ServerConfiguration::instance()->email;
        $mail = new \PHPMailer();

        $mail->isSMTP();

        $mail->Host     = $ses_info['host'];
        $mail->Port     = $ses_info['port'];
        $mail->SMTPAuth = $ses_info['auth'];
        $mail->Username = $ses_info['username'];
        $mail->Password = $ses_info['password'];
        $mail->setFrom($ses_info['sender'], $ses_info['sender_info']);
        $mail->addReplyTo($ses_info['receiver']);
        $mail->CharSet = 'UTF-8';
        $mail->IsHTML(true);
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
            minfo("Message sent successfully to %s " , implode("|",$emails));
        }

    }
}