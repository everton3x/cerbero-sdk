<?php

namespace Cerbero\Exception;

class UserNotFoundException extends ResourceNotFundException
{
    public function __construct(string $identity)
    {
        return parent::__construct($identity);
    }
}