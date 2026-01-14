<?php

namespace Cerbero\Core\Config;

final class Config
{
    public function __construct(
        public readonly string $dbDsn,
        public readonly ?string $dbUser = null,
        public readonly ?string $dbPassword = null,
    )
    {
        
    }

}