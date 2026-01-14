<?php

namespace Cerbero\Exception;

use DomainException;

class ForbiddenException extends DomainException
{
    public function __construct()
    {
        return parent::__construct('Forbidden', 403);
    }
}