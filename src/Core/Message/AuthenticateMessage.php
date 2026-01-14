<?php

namespace Cerbero\Core\Message;

use Cerbero\Core\Entity\User;
use Exception;

class AuthenticateMessage
{
    
    public function __construct(
        public readonly bool $success,
        public readonly ?string $identity = null,
        public readonly ?string $utoken = null,
        public readonly array $errors = []
    )
    {
        
    }
}