<?php

namespace Cerbero\Exception;

use DomainException;
use Throwable;

class DbException extends DomainException
{
    public function __construct(string $message = "", int $code = 0, Throwable|null $previous = null)
    {
        return parent::__construct($message, $code, $previous);
    }
}