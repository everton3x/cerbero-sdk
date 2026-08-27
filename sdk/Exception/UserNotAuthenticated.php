<?php

namespace Cerbero\Sdk\Exception;

use RuntimeException;

class UserNotAuthenticated extends RuntimeException {
    public function __construct(public readonly string $userId) {
        parent::__construct('User not authenticated.');
    }
}