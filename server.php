<?php

require_once __DIR__ . '/bootstrap.php';

use Yyg\Core\Server;
use Yyg\Configuration\ServerConfiguration;
use Oasis\Mlib\Logging\LocalFileHandler;

(new LocalFileHandler(ServerConfiguration::instance()->log_path))->install();

//\Yyg\Configuration\ServerConfiguration::instance()->dumpConfig();

Server::getInstance()->run();