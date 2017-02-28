<?php
/**
 * Created by PhpStorm.
 * User: og
 * Date: 17/2/24
 * Time: 下午4:13
 */

namespace Yyg\Tasks;

use Clue\React\Redis\Client;
interface TaskInterface
{
    public static function execute(array $task, Client $res_client);
}