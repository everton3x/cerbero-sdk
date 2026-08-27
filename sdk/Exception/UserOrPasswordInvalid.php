<?php

namespace Cerbero\Sdk\Exception;

use RuntimeException;

class UserOrPasswordInvalid extends RuntimeException {
    public function __construct(
            public readonly string $userId
    ) {
        parent::__construct('User or password invalid.');
    }
}