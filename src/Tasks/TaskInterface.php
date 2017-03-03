<?php
/**
 * Created by PhpStorm.
 * User: og
 * Date: 17/2/24
 * Time: 下午4:13
 */

namespace Yyg\Tasks;

interface TaskInterface
{
    public static function execute(array $task);
}