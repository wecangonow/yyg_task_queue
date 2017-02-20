<?php
/**
 * Created by PhpStorm.
 * User: og
 * Date: 17/2/20
 * Time: 上午10:32
 */

namespace Yyg\Core;

class Packet
{
    const HEADER_SIZE   = 4;
    const HEADER_STRUCT = "Nlength";
    const HEADER_PACK   = "N";

    public static function encode($data)
    {
        return pack(self::HEADER_PACK, strlen($data)) . $data;
    }

    public static function decode(&$data)
    {
        $header = substr($data, 0, self::HEADER_SIZE);
        $data = substr($data, self::HEADER_SIZE);

        return $header ? unpack(self::HEADER_STRUCT, $header) : "";
    }
}