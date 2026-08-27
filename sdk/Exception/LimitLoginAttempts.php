<?php

namespace Cerbero\Sdk\Exception;

use RuntimeException;

/**
 * Exceção lançada quando o limite máximo de tentativas de login do usuário é excedido.
 *
 * @package Cerbero\Sdk\Exception
 */
class LimitLoginAttempts extends RuntimeException {
    /**
     * Construtor da exceção LimitLoginAttempts.
     *
     * @param string $userId Identificador do usuário que atingiu o limite de tentativas.
     */
    public function __construct(public readonly string $userId) {
        parent::__construct('Maximum login attempts reached.');
    }
}