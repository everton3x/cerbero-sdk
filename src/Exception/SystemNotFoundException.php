<?php

namespace Cerbero\Exception;

class SystemNotFoundException extends ResourceNotFundException
{
    public function __construct(string $stoken)
    {
        return parent::__construct($stoken);
    }
}