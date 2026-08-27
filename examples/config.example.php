<?php

return [
    'pdoDsn' => 'sqlite:./cerbero-example.db',
    'pdoUser' => null,
    'pdoPass' => null,
    'pdoOptions' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ],
];