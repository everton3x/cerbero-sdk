<?php

namespace Cerbero\Sdk\Exception;

use RuntimeException;

/**
 * Exceção lançada quando a autenticação do usuário falha ou a sessão é inválida.
 *
 * @package Cerbero\Sdk\Exception
 */
class UserNotAuthenticated extends RuntimeException {
    /**
     * Construtor da exceção UserNotAuthenticated.
     *
     * @param string $userId Identificador do usuário que não está autenticado.
     */
    public function __construct(public readonly string $userId) {
        parent::__construct('User not authenticated.');
    }
}