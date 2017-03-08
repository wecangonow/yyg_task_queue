<?php

define("PROJECT_DIR", __DIR__);
use Yyg\Configuration\ServerConfiguration;


require_once __DIR__ . "/vendor/autoload.php";

ServerConfiguration::instance()->load();
?>
