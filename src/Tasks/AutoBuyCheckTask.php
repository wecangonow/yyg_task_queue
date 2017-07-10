<?php
/**
 * Created by PhpStorm.
 * User: og
 * Date: 17/6/29
 * Time: 上午11:03
 */

namespace Yyg\Tasks;

use Oasis\Mlib\Logging\LocalFileHandler;
use Yyg\Core\ExecutionTime;

class AutoBuyCheckTask implements TaskInterface
{
    public static $buy_numbers = [
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 7, 1, 2, 7, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 23, 4, 10, 2, 7, 5, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 2, 2, 2, 2, 2,
        2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 4, 10, 5, 7, 1, 5, 1, 5, 1, 1, 5,
        1, 2, 15, 1, 2, 10, 1, 5, 10, 2, 5, 1, 1, 2, 1, 5, 60, 2, 10, 29, 1, 10, 10, 8, 5, 2, 5, 16, 2, 1, 11, 11, 53, 5, 1, 1, 2, 19, 8, 1, 1,
        2, 1, 10, 2, 2, 1, 2, 5, 10, 1, 14, 2, 1, 1, 4, 1, 1, 1, 1, 1, 5, 1, 5, 10, 4, 3, 1, 10, 1, 1, 1, 2, 1, 2, 8, 2, 6, 9, 2, 2, 4, 2, 1, 4,
        15, 4, 4, 1, 7, 6, 1, 6, 1, 3, 2, 2, 5, 2, 6, 1, 1, 3, 1, 18, 1, 5, 5, 1, 2, 1, 10, 1, 5, 2, 2, 1, 9, 10, 1, 2, 5, 5, 30, 9, 2,
        3, 1, 2, 6, 5, 7, 10, 2, 2, 2, 1, 30, 8, 4, 1, 3, 2, 15, 10, 10, 3, 90, 4, 5, 10, 10, 1, 1, 1, 1, 10, 6, 80, 3, 1, 1, 2, 5, 2, 1, 1, 25,
        1, 1, 3, 1, 6, 3, 1, 33, 5, 5, 5, 2, 1, 2, 1, 14, 3, 5, 22, 1, 1, 10, 36, 5, 1, 1, 1, 2, 2, 1, 1, 1, 10, 5, 2, 1, 1, 1, 10, 10, 11, 2, 2, 50, 8, 1,
        7, 1, 10, 1, 1, 8, 1, 2, 100, 10, 2, 2, 1, 2, 5, 1, 1, 2, 3, 15, 2, 2, 5, 1, 1, 5, 1, 1, 1, 5, 1, 1, 4, 5, 1, 2, 10, 2, 1, 9, 2, 3, 5,
        5, 10, 2, 6, 3, 1, 2, 2, 2, 5, 2, 2, 2, 2, 5, 1, 1, 1, 6, 7, 2, 1, 40, 7, 1, 6, 8, 2, 10, 2, 5, 5, 9, 2, 1, 2, 5, 5, 1, 3, 1, 1, 15, 1,
        1, 2, 3, 1, 1, 1, 1, 10, 1, 5, 2, 1, 2, 1, 5, 7, 5, 1, 1, 1, 2, 3, 1, 10, 1, 1, 1, 5, 5, 1, 2, 10, 5, 2, 1, 1, 2, 1, 1, 5, 1, 2, 2, 5, 1,
        2, 8, 5, 2, 3, 2, 15, 3, 2, 5, 1, 1, 9, 1, 2, 2, 10, 2, 15, 10, 15, 20, 5, 1, 10, 1, 2, 15, 1, 10, 5, 1, 2, 10, 2, 1, 5, 1, 5, 10, 1, 1, 10, 2, 1,
        1, 10, 1, 1, 1, 1, 1, 15, 3, 1, 1, 1, 5, 2, 10, 2, 1, 10, 2, 1, 1, 3, 7, 1, 1, 1, 5, 2, 1, 3, 2, 10, 1, 7, 1, 10, 9, 5, 1, 40, 1, 3, 2, 1, 10, 1, 1,
        3, 4, 1, 5, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 9, 5, 2, 3, 1, 2, 1, 2, 11, 1, 1, 32, 1, 5, 2, 4, 3,
        1, 2, 4, 1, 5, 1, 5, 1, 34, 2, 6, 17, 5, 16, 10, 2, 1, 10, 10, 5, 1, 1, 2, 3, 9, 8, 10, 7, 2, 8, 5, 17, 10, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 10,
    ];

    public static function execute(array $task)
    {
        ExecutionTime::Start();

        global $configs, $redis;

        (new LocalFileHandler($configs['log_path']))->install();

        $tasks = self::GetAllTasks();

        if (count($tasks) > 0) {

            foreach($tasks as $task) {

                $exec_time = $task['exec_time'];
                $gid = $task['gid'];
                $task_id = $task['id'];

                if ($configs['is_debug']) {
                    mdebug(
                        "goods id %d task id %d : run_hour is %d execute time is %s - time remaining is %ds",
                        $gid,
                        $task_id,
                        $task['run_hour'],
                        date("Y-m-d H:i:s", $exec_time),
                        $exec_time - time()
                    );
                }

                if((int)$exec_time > (int)time()) {
                    continue;
                }

                $ignore_setting = self::getTaskCurrentNperIdAndIgnorePercent($gid);
                if ($ignore_setting) {
                    $arr        = json_decode(explode("_", $ignore_setting)[1], true);
                    $rand_index = rand(0, 9);
                    $state      = $arr[$rand_index];
                    mdebug(
                        "gid %d ignore setting is %s | rand_index is %d choose state is %s",
                        $gid,
                        $ignore_setting,
                        $rand_index,
                        $state
                    );
                    if ($state) {
                        self::sync_task($task);

                        return;
                    }
                }


                if ($task['join_type'] == 3) {
                    $join_type = rand(0, 2);
                }
                else if ($task['join_type'] == 2) {
                    $join_type = rand(1, 2);
                }
                else {
                    $join_type = 0;
                }

                $buy_num = self::$buy_numbers[mt_rand(0, count(self::$buy_numbers) - 1)];

                if ($task['price'] < 500 && $buy_num > 10) {
                    $buy_num = mt_rand(1, 2);
                }

                if ($buy_num != 1 || $buy_num != 2) {

                    $last_buy_num_key = "last_buy_times_" . $task['id'];

                    $last_buy_num = $redis->get($last_buy_num_key);

                    if ($last_buy_num) {
                        if ($last_buy_num == $buy_num) {
                            $buy_num = mt_rand(1, 2);
                        }
                        else {
                            $redis->set($last_buy_num_key, $buy_num);
                        }
                    }
                    else {
                        $redis->set($last_buy_num_key, $buy_num);
                    }

                }

                $task['buy_times'] = $buy_num;
                $robot_info = self::GetRandomRobot();

                $request_data = [
                    'type' => 'robotBuy',
                    'argv' => [
                        'request_data' =>

                            [
                                'id'        => $task['id'],
                                'uid'       => $robot_info['id'],
                                'gid'       => $task['gid'],
                                'num'       => $task['buy_times'],
                                'join_type' => $join_type,
                            ],
                        'task_detail'  => $task,
                        'robot_info'  => $robot_info
                    ]
                ];

                $task_data = json_encode($request_data);
                $redis->lpush("message_queue", $task_data);
                mdebug("message queue got task %s", $task_data);
            }
        }
        ExecutionTime::End();
        minfo("%s::execute spend %s ", get_called_class(), ExecutionTime::ExportTime());
    }

    public static function GetAllTasks()
    {
        global $db;
        $hour = date("G", time());

        $sql = "select p.id,p.exec_record_times,p.speed_x,p.gid,p.min_time,p.max_time,p.join_type,p.exec_time,p.run_hour, g.price, g.unit_price
                    from sp_rt_regular p
                    right join sp_goods g on p.gid = g.id
                    right join sp_nper_list n  on p.gid = n.pid
                     where run_hour = $hour and enable = 1 and n.status = 1 order by rand()";
        $ret = $db->query($sql);

        return $ret;

    }

    public static function GetRandomRobot()
    {
        global $redis, $db;

        $robot_sql = "select id, nick_name from sp_users where type = -1 and status = 1";

        $cache_time       = 3600 * 24;
        $robots_cache_key = "buying_robot_list";

        $ret = $redis->get($robots_cache_key);

        if (!$ret) {
            $list = $db->query($robot_sql);

            $redis->set($robots_cache_key, serialize($list));
            $redis->expire($robots_cache_key, $cache_time);
        }
        else {
            $list = unserialize($ret);
        }

        return $list[array_rand($list)];
    }

    public static function setTaskCurrentNperIdAndIgnorePercent($gid, $nper_id)
    {
        global $redis;
        $ignore_number_arr = [1, 2, 3, 4, 5, 6, 7, 8, 9];
        $key               = "goods:current:nper:" . $gid;

        $ignore_number = $ignore_number_arr[rand(0, 6)];

        $true_count = 0;
        $ignore_arr = [];
        for ($i = 0; $i < 10; $i++) {
            if ($true_count < $ignore_number) {
                $ignore_arr[] = 1;
                $true_count++;
            }
            else {
                $ignore_arr[] = 0;
            }
        }
        shuffle($ignore_arr);
        $ignore_string = json_encode($ignore_arr);
        $value         = $nper_id . "_" . $ignore_string;

        mdebug("gid %d , nper_id %d setting ignore to %s | ignore num is %d", $gid, $nper_id, $value, $ignore_number);

        $redis->set($key, $value);
        $redis->expire($key, 3600*24*30);

        return $value;
    }

    public static function getTaskCurrentNperIdAndIgnorePercent($gid, $nper_id = 0)
    {
        global $redis;

        $key = "goods:current:nper:" . $gid;

        if ($redis->exists($key)) {
            return $redis->get($key);
        }
        else {
            if ($nper_id) {
                return self::setTaskCurrentNperIdAndIgnorePercent($gid, $nper_id);
            }
            else {
                return false;
            }
        }
    }

    public static function sync_task($data)
    {
        global $db;
        $gid = $data['gid'];
        $time = time();
        $random_sec = intval(rand($data['min_time'], $data['max_time']) / $data['speed_x']);

        $time = $random_sec + (int)$time;


        $up_data['exec_time']         = $time;
        $up_data['exec_record_times'] = (int)$data['exec_record_times'] + 1;
        $up_data['update_time']       = time();

        $row_count = $db->update('sp_rt_regular')->cols($up_data)->where('gid=' . $gid)->query();

        if($row_count > 0) {
            mdebug("gid is %d  next run time add secs is %d", $gid, $random_sec);
        }
    }

}

