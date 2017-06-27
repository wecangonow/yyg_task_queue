<?php
/**
 * Created by PhpStorm.
 * User: og
 * Date: 17/6/26
 * Time: 下午6:43
 */

namespace Yyg\Tasks;

use Yyg\Core\GoogleUrlApi;

class ShortUrlTask  implements TaskInterface
{
    public static function execute(array $task)
    {
        $uid = $task['argv']['uid'];
        $basic_url = "http://1.1rmhunt.com/trace_url.php?uid=";

        $url = $basic_url . $uid;

        $google_api = new GoogleUrlApi();

        $short_url = $google_api->shorten($url);

        echo $short_url . "\n";
    }

}