<?php

namespace Cerbero\Sdk\Exception;

use RuntimeException;

/**
 * Exceção lançada quando as credenciais (usuário ou senha) são inválidas.
 *
 * @package Cerbero\Sdk\Exception
 */
class UserOrPasswordInvalid extends RuntimeException
{
    /**
     * Construtor da exceção UserOrPasswordInvalid.
     *
     * @param string $userId Identificador do usuário cujas credenciais são inválidas.
     */
    public function __construct(
        public readonly string $userId
    ) {
        parent::__construct('User or password invalid.');
    }
}
