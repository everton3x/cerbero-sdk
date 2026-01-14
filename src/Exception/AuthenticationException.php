<?php

namespace Cerbero\Exception;

use DomainException;

class AuthenticationException extends DomainException
{
    public function __construct()
    {
        return parent::__construct('Unauthenticated', 401);
    }
}