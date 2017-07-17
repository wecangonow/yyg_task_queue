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
        global $db, $configs;

        $uid = $task['argv']['uid'];
        $basic_url = $configs['short_url_basic'];

        $url = $basic_url . $uid;

        //$google_api = new GoogleUrlApi();

        //$short_url = $google_api->shorten($url);


        $sql = "update sp_users set share_url = '$url' where id = $uid";

        $ret = $db->query($sql);

        if($ret) {
            mdebug("set users id %d share url to %s successfully | origin url is %s", $uid, $url, $url);
        } else {
            mdebug("set users id %d share url to %s failed | origin url is %s", $uid, $url, $url);
        }

    }

}
