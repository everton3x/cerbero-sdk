<?php

namespace Tests;

use Cerbero\Sdk\Cerbero;
use PHPUnit\Framework\TestCase as BaseTestCase;
use PDO;

abstract class TestCase extends BaseTestCase
{
    protected ?Cerbero $crb = null;

    protected function crb(): Cerbero
    {
        if(is_null($this->crb)){
            $this->crb = new Cerbero(getConfig());
        }
        return $this->crb;
    }
}
