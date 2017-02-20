<?php
use Yyg\Configuration\ServerConfiguration;

define("PROJECT_DIR", __DIR__);

require_once __DIR__ . "/vendor/autoload.php";

ServerConfiguration::instance()->load();
?>
