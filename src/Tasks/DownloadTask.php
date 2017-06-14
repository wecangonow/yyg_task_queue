<?php
/**
 * Created by PhpStorm.
 * User: og
 * Date: 17/2/28
 * Time: 上午10:51
 */

namespace Yyg\Tasks;

class DownloadTask implements TaskInterface
{

    public static function execute(array $task)
    {
        global $redis;

        if ((isset($task['argv']['category']) && $task['argv']['category'] == "real_download") || isset($task['category'])) {
            $download_file = self::getImg($task['argv']['download_url'], $task['argv']['save_path']);
            minfo("Task download image to : %s", $download_file);

        } else {

            $url       = $task['argv']['url'];
            $base_path = $task['argv']['path'];

            $response = self::getJson($url);

            if ($response) {

                foreach ($response as $rate) {

                    $user_id     = $rate['id'];
                    $pics        = isset($rate['pics']) && is_array($rate['pics']) ? $rate['pics'] : [];
                    $append_pics = isset($rate['appendComment']['pics']) && is_array($rate['appendComment']['pics']) ?
                        $rate['appendComment']['pics'] : [];
                    $final_pics  = array_merge($pics, $append_pics);
                    $save_dir    = $base_path . $user_id . "/";

                    if (count($final_pics) > 0) {

                        if (!is_dir($save_dir)) {
                            if (false === @mkdir($save_dir, 0777, true)) {
                                throw new \RuntimeException(sprintf('Unable to create the %s directory', $save_dir));
                            }
                        }

                        foreach ($final_pics as $pic) {

                            $pic_url                          = "http:" . $pic;
                            $url_task                         = [];
                            $url_task['type']                 = "download";
                            $url_task['argv']['category']             = 'real_download';
                            $url_task['argv']['download_url'] = $pic_url;
                            $url_task['argv']['save_path']    = $save_dir;

                            $redis->lpush("message_queue", json_encode($url_task));
                        }
                    }
                    else {
                        minfo("Task download image : %s have no pics", $save_dir);
                    }

                }

            }
            else {

                $back_message = json_encode($task);

                //$redis->lpush("message_queue", $back_message);
                //
                //minfo("Task  failed send back to queue again %s ", $back_message);

            }
        }
    }

    private static function getImg($url, $path, $overwrite = false)
    {

        $imageName = basename($url);
        if (file_exists($path . $imageName) && !$overwrite) {
            return $path . $imageName;
        }
        $headers[]  = 'Accept: image/gif, image/x-bitmap, image/jpeg, image/pjpeg';
        $headers[]  = 'Connection: Keep-Alive';
        $headers[]  = 'Content-type: application/x-www-form-urlencoded;charset=UTF-8';
        $user_agent = 'php';
        $process    = curl_init($url);
        curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($process, CURLOPT_HEADER, 0);
        curl_setopt($process, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($process, CURLOPT_TIMEOUT, 30);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
        $image = curl_exec($process);
        curl_close($process);
        file_put_contents($path . $imageName, $image);

        return $path . $imageName;
    }

    private static function getJson($base_url)
    {
        //$base_url = "https://rate.tmall.com/list_detail_rate.htm?sellerId=520&order=3&callback=jsonp&itemId=527355597126&currentPage=1&picture=1";

        $base_str  = file_get_contents($base_url);
        $base_str  = mb_convert_encoding($base_str, 'HTML-ENTITIES', "UTF-8");
        $final_str = str_replace("jsonp(", "", $base_str);

        $final_str = rtrim($final_str, ")");

        $arr          = json_decode($final_str, true);
        $json_err_msg = json_last_error_msg();
        $json_err     = json_last_error();

        if ($json_err == 0) {

            return $arr['rateDetail']['rateList'];

        }
        else {

            merror("Json decode error  is %s and url is %s ", $json_err_msg, $base_url);

            return false;
        }

    }

}

