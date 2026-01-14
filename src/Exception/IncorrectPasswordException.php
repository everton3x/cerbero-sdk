<?php

namespace Cerbero\Exception;

use DomainException;
use Throwable;

class IncorrectPasswordException extends DomainException
{
    public function __construct(string $identity)
    {
        return parent::__construct($identity);
    }
}