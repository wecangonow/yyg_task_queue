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
    public static function send_email($real_email_content, $task)
    {
        global $configs;

        $email_config = $configs['services']['email'];
        $mail         = new \PHPMailer();

        $mail->isSMTP();

        $mail->Host      = $email_config['host'];
        $mail->Port      = $email_config['port'];
        $mail->SMTPAuth  = $email_config['auth'];
        $mail->Username  = $email_config['username'];
        $mail->Password  = $email_config['password'];
        //$mail->SMTPDebug = 2;
        $mail->setFrom($email_config['info']['sender'], $email_config['info']['sender_info']);
        $mail->addReplyTo($email_config['info']['receiver']);
        $mail->CharSet = 'UTF-8';
        $mail->IsHTML($real_email_content['is_html']);
        $mail->Subject = $real_email_content['subject'];
        $mail->Body    = $real_email_content['body'];

        // 上线后需要移除
        $email = $real_email_content['email'];
        $email = "haozhongzhi@brotsoft.com";

        $mail->addBCC($email);

        if (!$mail->send()) {
            merror("Mailer Error: %s ", $mail->ErrorInfo);
        }
        else {
            self::cache_email_times_in_three_days($email);
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

        $continue = true;
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
                $uid                         = $task['argv']['uid'];
                $win_record_id               = $task['argv']['win_record_id'];
                $real_email_content['email'] = self::get_email_by_id($uid);
                $good_name                   = self::get_good_name_by_win_record_id($win_record_id);
                $real_email_content['body']  = str_replace("{{good_name}}", $good_name, $real_email_content['body']);
                break;
            case "shipped":
                $win_record_id               = $task['argv']['win_record_id'];
                $real_email_content['email'] = self::get_email_with_win_record_id($win_record_id);
                $good_name                   = self::get_good_name_by_win_record_id($win_record_id);
                $real_email_content['body']  = str_replace("{{good_name}}", $good_name, $real_email_content['body']);
                break;
            case "show_order":
                $show_order_ids = trim($task['argv']['show_order_ids'],',');
                self::show_order_email_and_notify($show_order_ids, $real_email_content, $task);
                $continue = false;
                break;

        }


        if($continue) {

            if (is_null($real_email_content['email'])) {
                mdebug("can not find user's email address : request data is %s", json_encode($task));

                return;
            }
            var_dump($real_email_content);
            self::send_email($real_email_content, $task);
        }
    }

    public static function get_email_with_win_record_id($win_record_id)
    {
        global $db;
        $sql = "select email from sp_bind_email where uid = (select luck_uid  from sp_win_record where id = $win_record_id)";
        $info = $db->row($sql);
        if(isset($info['email'])) {
            return  $info['email'];
        } else {
            return null;
        }
    }

    public static function show_order_email_and_notify($show_order_ids, $real_email_content, $task)
    {
        global $db, $configs;

        $sql  = "select reg_token from sp_reg_token where (`group` = 'android' or `group` is null) and uid in (select uid from sp_show_order where id in ($show_order_ids))";
        $info = $db->query($sql);

        if (count($info) > 0) {

            $title        = $configs['services']['android_push']['tpl']['show_order']['title'];
            $message      = $configs['services']['android_push']['tpl']['show_order']['message'];
            $send_message = ['title' => $title, 'message' => $message];

            foreach ($info as $v) {
                if(NoticeTask::verify_limit($v['reg_token'])){
                    $tokens[] = $v['reg_token'];
                }
            }

            NoticeTask::send_gcm_notify($tokens, $send_message);
        }

        $sql  = "select `email` from sp_users where uid in (select uid from sp_show_order where id in ($show_order_ids))";
        $info = $db->query($sql);

        foreach ($info as $v) {
            $email = !is_null($v['email']) ? $v['email'] : null;
            if ($email == null) {
                continue;
            }
            else {
                $real_email_content['email'] = $email;
                self::send_email($real_email_content, $task);
            }
        }

        return;

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
            "select email  from sp_bind_email where uid = $id"
        );

        return !is_null($email_info['email']) ? $email_info['email'] : null;

    }

    public static function cache_email_times_in_three_days($email)
    {
        global $redis;
        $prefix = "email_count_in_three_days:#";
        $key = $prefix . trim($email);
        if(!$redis->exists($key)){
            $redis->set($key,1);
            $redis->expire($key, 3600 * 24 * 3);
        } else {
            $redis->incr($key);
        }
        mdebug("%s value is %d", $key, $redis->get($key));

    }
}
