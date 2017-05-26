<?php
/**
 * Created by PhpStorm.
 * User: og
 * Date: 17/2/24
 * Time: 下午4:11
 */

namespace Yyg\Tasks;

require_once PROJECT_DIR . '/vendor/phpmailer/phpmailer/PHPMailerAutoload.php';

class EmailTask implements TaskInterface
{
    public static function send_email($email_config, $real_email_content, $task)
    {
        $mail = new \PHPMailer();

        $mail->isSMTP();

        $mail->Host      = $email_config['host'];
        $mail->Port      = $email_config['port'];
        $mail->SMTPAuth  = $email_config['auth'];
        $mail->Username  = $email_config['username'];
        $mail->Password  = $email_config['password'];
        $mail->SMTPDebug = 2;
        $mail->setFrom($email_config['info']['sender'], $email_config['info']['sender_info']);
        $mail->addReplyTo($email_config['info']['receiver']);
        $mail->CharSet = 'UTF-8';
        $mail->IsHTML($real_email_content['is_html']);
        $mail->Subject = $real_email_content['subject'];
        $mail->Body    = $real_email_content['body'];

        $mail->addBCC($real_email_content['email']);

        if (!$mail->send()) {
            merror("Mailer Error: %s ", $mail->ErrorInfo);
        }
        else {
            minfo(
                "Task type %s category %s  successfully to %s ",
                $task['type'],
                $task['argv']['category'],
                $real_email_content['email']
            );
        }

    }

    public static function execute(array $task)
    {
        global $configs;

        $email_config = $configs['services']['email'];

        $category = $task['argv']['category'];

        $real_email_content            = [];
        $real_email_content['subject'] = $email_config['info']['tpl'][$category]['subject'];
        $real_email_content['body']    = $email_config['info']['tpl'][$category]['body'];
        $real_email_content['is_html'] = $email_config['info']['tpl'][$category]['is_html'];

        switch ($category) {
            case "register":
                $real_email_content['email'] = $task['argv']['email'];
                break;
            case "payment":
                $uid                         = $task['argv']['uid'];
                $order_id                    = $task['argv']['order_id'];
                $real_email_content['email'] = self::get_email_by_id($uid);
                $pay_time                    = self::get_pay_time_by_order($order_id);
                $real_email_content['body']  = str_replace("{{pay_time}}", $pay_time, $real_email_content['body']);
                break;
            case "receipt":
                $uid           = $task['argv']['uid'];
                $win_record_id = $task['argv']['win_record_id'];
                $real_email_content['email'] = self::get_email_by_id($uid);
                $good_name = self::get_good_name_by_win_record_id($win_record_id);
                $real_email_content['body']  = str_replace("{{good_name}}", $good_name, $real_email_content['body']);
                break;
            case "shipped":
                $real_email_content['email'] = $task['argv']['email'];
                $win_record_id = $task['argv']['win_record_id'];
                $good_name = self::get_good_name_by_win_record_id($win_record_id);
                $real_email_content['body']  = str_replace("{{good_name}}", $good_name, $real_email_content['body']);
                break;

        }

        if (!isset($real_email_content['email'])) {
            mdebug("can not find user's email address : request data is %s", json_encode($task));

            return;
        }

        self::send_email($email_config, $real_email_content, $task);
    }

    public static function get_good_name_by_win_record_id($win_record_id)
    {
        global $db;

        $good_info = $db->row(
            "select name from sp_goods g join sp_win_record w on w.goods_id = g.id where w.id =" . $win_record_id
        );

        return $good_info['name'];

    }

    public static function get_pay_time_by_order($order_id)
    {
        global $db;

        $pay_info = $db->row(
            "select pay_time from sp_order_list_parent where order_id=" . strval($order_id)
        );

        return date("Y-m-d H:i:s", $pay_info['pay_time'] / 1000);
    }

    public static function get_email_by_id($id)
    {
        global $db;
        $email_info = $db->row(
            "select email, email_3rd from sp_users where id = $id"
        );

        return !is_null($email_info['email']) ? $email_info['email'] :
            (!is_null($email_info['email_3rd']) ? $email_info['email_3rd'] : null);

    }
}