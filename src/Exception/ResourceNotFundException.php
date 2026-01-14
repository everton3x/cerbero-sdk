<?php

namespace Cerbero\Exception;

class ResourceNotFundException extends \InvalidArgumentException
{
    public function __construct(string $resource)
    {
        return parent::__construct($resource);
    }
}