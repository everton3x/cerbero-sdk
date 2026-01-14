<?php

namespace Cerbero\Core\Config;

use Cerbero\Exception\ConfigException;
use Cerbero\Exception\ResourceNotFundException;

final class ConfigLoader
{
    public static function loadFromIniFile(string $filepath): Config
    {
        if(!file_exists($filepath)) throw new ResourceNotFundException($filepath);

        $data = parse_ini_file($filepath, process_sections: true, scanner_mode: INI_SCANNER_TYPED);

        if($data === false) throw new ConfigException("Fails to load configurations from $filepath");

        if(!key_exists('DSN', $data['db'])) throw new ConfigException("DB.DSN configuration not found!");

        key_exists('USER', $data['db'])? $dbUser = $data['db']['USER'] : $dbUser = null;
        key_exists('PASSWORD', $data['db'])? $dbPassword = $data['db']['PASSWORD'] : $dbPassword = null;

        $config = new Config(
            dbDsn: $data['db']['DSN'],
            dbUser: $dbUser,
            dbPassword: $dbPassword
        );

        return $config;
    }
}