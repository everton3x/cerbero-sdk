<?php

namespace Cerbero\Core\Db;

use PDO;

final class Database
{
    public static function getHandler(string $dsn, ?string $user = null, ?string $password = null): \PDO
    {
        return new PDO($dsn, $user, $password, [
            PDO::ATTR_PERSISTENT => true
        ]);
    }
}