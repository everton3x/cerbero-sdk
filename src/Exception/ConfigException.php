<?php

namespace Cerbero\Exception;

use DomainException;
use Throwable;

class ConfigException extends DomainException
{
    public function __construct(string $message = "")
    {
        return parent::__construct($message);
    }
}