<?php

require_once __DIR__ . '/bootstrap.php';

require_once "url_arr.php";

use Yyg\Configuration\ServerConfiguration;
use Oasis\Mlib\Logging\LocalFileHandler;

function getJson($base_url)
{

    $base_str  = file_get_contents($base_url);
    $base_str  = mb_convert_encoding($base_str, 'HTML-ENTITIES', "UTF-8");
    $final_str = str_replace("jsonp(", "", $base_str);
    //$final_str = str_replace("\r\n", "", $final_str);
    $final_str = rtrim($final_str, ")");
    file_put_contents("products_json.json", $final_str);

    $arr          = json_decode($final_str, true);
    $json_err_msg = json_last_error_msg();
    $json_err     = json_last_error();

    if ($json_err == 0) {
        $lastPage  = $arr['rateDetail']['paginator']['lastPage'];
        $rate_list = $arr['rateDetail']['rateList'];

        $ret['pagesNum'] = $lastPage;
        $ret['rateList'] = $rate_list;

        return $ret;

    }
    else {
        merror("Json decode error  is %s and url is %s ", $json_err_msg, $base_url);
    }

}

(new LocalFileHandler(ServerConfiguration::instance()->log_path))->install();

$base_dir = "/data/download/images";

$client = stream_socket_client('tcp://127.0.0.1:6161');

foreach ($tasks as $k => $p) {

    $url   = $k;
    $count = $p['count'];
    $path  = $base_dir . "/" . trim($p['name'], " ") . "/" . $p['itemId'] . "/";

    for ($i = 1; $i <= $count; $i++) {
        $repalce = "currentPage=$i";
        $search  = "currentPage=1";
        $new_url = str_replace($search, $repalce, $url);

        $url_task                 = [];
        $url_task['type']         = "download";
        $url_task['argv']['url']  = $new_url;
        $url_task['argv']['path'] = $path;

        fwrite($client, json_encode($url_task) . "\n");
        minfo("server response: %s", fread($client, 100));

    }

}

fclose($client);

