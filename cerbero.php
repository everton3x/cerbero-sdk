<?php

use Cerbero\Core\Cerbero;
use Cerbero\Core\Config\Config;
use Cerbero\Core\Db\Database;

require __DIR__.'/vendor/autoload.php';

Cerbero::init(configFile: __DIR__.'/config/cerbero.ini');

/* function testc()
{
    $config = Cerbero\Core\Config\ConfigLoader::loadFromIniFile(__DIR__.'/config/cerbero.ini');

    return Database::getHandler($config->dbDsn);
} */