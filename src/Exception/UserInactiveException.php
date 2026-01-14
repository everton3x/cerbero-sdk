<?php

namespace Cerbero\Exception;

use DomainException;

class UserInactiveException extends DomainException
{
    public function __construct(string $identity)
    {
        return parent::__construct($identity);
    }
}