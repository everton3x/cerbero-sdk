<?php

namespace Cerbero\Sdk\Exception;

use RuntimeException;

/**
 * Exceção lançada quando o usuário não possui autorização de acesso ao sistema.
 *
 * @package Cerbero\Sdk\Exception
 */
class UserNotAuthorized extends RuntimeException {
    /**
     * Construtor da exceção UserNotAuthorized.
     *
     * @param string $userId Identificador do usuário não autorizado.
     * @param string $systemSlug Identificador slug do sistema solicitado.
     */
    public function __construct(
            public readonly string $userId,
            public readonly string $systemSlug
    ) {
        parent::__construct('User not authorized for system.');
    }
}