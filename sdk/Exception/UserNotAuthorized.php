<?php

namespace Cerbero\Sdk\Exception;

use RuntimeException;

class UserNotAuthorized extends RuntimeException {
    public function __construct(
            public readonly string $userId,
            public readonly string $systemSlug
    ) {
        parent::__construct('User not authorized for system.');
    }
}