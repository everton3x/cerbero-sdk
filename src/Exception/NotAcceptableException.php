<?php

namespace Cerbero\Exception;

use DomainException;

class NotAcceptableException extends DomainException
{
    public function __construct()
    {
        return parent::__construct('Not Acceptable', 406);
    }
}