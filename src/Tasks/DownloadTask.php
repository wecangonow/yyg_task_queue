<?php
/**
 * Created by PhpStorm.
 * User: og
 * Date: 17/2/28
 * Time: 上午10:51
 */

namespace Yyg\Tasks;

use Clue\React\Redis\Client;

class DownloadTask implements TaskInterface
{

    public static function execute(array $task, Client $res_client)
    {
        $url = $task['argv']['url'];
        $base_path = $task['argv']['path'];

        $response = self::getJson($url);

        if($response) {
            //foreach($json['rateList'] as $rate) {
            //    $user_id = $rate['id'];
            //    $pics = $rate['pics'];
            //    $save_dir = $base_dir . "/" . $product_name_dir . "/" . $itemId . "/" . $user_id;
            //    foreach($pics as $pic) {
            //        $task['type'] = "download";
            //        $task['argv']['path'] = $save_dir;
            //        $task['argv']['url'] = "http://" . $pic;
            //
            //        minfo("Task download data: %s", json_encode($task));
            //    }
            //}

        } else {
            $res_client->lpush("message_queue", json_encode($task));
            minfo("Task type %s  failed send back to queue again %s " , $task['type'], json_encode($task));
        }



        $filename = self::getImg($task['argv']['url'], $task['argv']['path']);

    }

    private static function getImg($url, $path, $overwrite = false) {

        $imageName= basename($url);
        if(file_exists($path.$imageName) && !$overwrite) {
            return $path.$imageName;
        }
        $headers[] = 'Accept: image/gif, image/x-bitmap, image/jpeg, image/pjpeg';
        $headers[] = 'Connection: Keep-Alive';
        $headers[] = 'Content-type: application/x-www-form-urlencoded;charset=UTF-8';
        $user_agent = 'php';
        $process = curl_init($url);
        curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($process, CURLOPT_HEADER, 0);
        curl_setopt($process, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($process, CURLOPT_TIMEOUT, 30);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
        $image = curl_exec($process);
        curl_close($process);
        file_put_contents($path.$imageName,$image);

        return $path.$imageName;
    }

    private static function getJson($base_url) {

        $base_str = file_get_contents($base_url);
        $base_str = mb_convert_encoding($base_str, 'HTML-ENTITIES', "UTF-8");
        $final_str = str_replace("jsonp(", "", $base_str);
        //$final_str = str_replace("\r\n", "", $final_str);
        $final_str = rtrim($final_str, ")");
        file_put_contents("products_json.json", $final_str);

        $arr = json_decode($final_str, true);
        $json_err_msg = json_last_error_msg() ;
        $json_err = json_last_error() ;


        if($json_err == 0){

           return $arr['rateDetail']['rateList'];

        } else {

            merror("Json decode error  is %s and url is %s ", $json_err_msg, $base_url);
            return false;
        }

    }

}

