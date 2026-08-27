<?php

use Cerbero\Sdk\Cerbero;

function getCacheDb(): string
{
    return './cache/test.db';
}
function getConfig(): array
{
    $cache = getCacheDb();
    return [
        'pdoDsn' => "sqlite:$cache",
        'pdoUser' => null,
        'pdoPass' => null,
        'pdoOptions' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ],
    ];
}

function prepareDb(): void
{
    if(file_exists(getCacheDb())){
        unlink(getCacheDb());
    }

    $config = getConfig();

    $pdo = new PDO($config['pdoDsn'], $config['pdoUser'], $config['pdoPass'], $config['pdoOptions']);

    foreach([
        './migrations/example-db.sql',
        './migrations/populate-example.sql',
    ] as $migration){
        $sql = file_get_contents($migration);
        $pdo->exec($sql);
    }
    unset($pdo);
}

function getCerberoInstance(): Cerbero
{
    return new Cerbero(getConfig());
}