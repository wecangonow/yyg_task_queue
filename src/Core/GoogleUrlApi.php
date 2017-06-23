<?php
/**
 * Created by PhpStorm.
 * User: og
 * Date: 17/6/15
 * Time: 上午10:19
 */

namespace Yyg\Core;

class GoogleUrlApi
{
    private $apiURL = "";

    public function __construct($key = "AIzaSyDNY5zKVapbYDikMsK1tz6bDLn1I4xqDew", $apiURL = "https://www.googleapis.com/urlshortener/v1/url")
    {
        $this->apiURL = $apiURL . '?key=' . $key;
    }


}