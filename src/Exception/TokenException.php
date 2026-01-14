<?php

namespace Cerbero\Exception;

use DomainException;

class TokenException extends DomainException
{
    public function __construct(string $token)
    {
        return parent::__construct($token);
    }
}