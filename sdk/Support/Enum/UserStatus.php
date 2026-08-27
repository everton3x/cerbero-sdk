<?php

namespace Cerbero\Sdk\Support\Enum;

/**
 * Representa os possíveis estados de um usuário no sistema.
 *
 * @package Cerbero\Sdk\Support\Enum
 */
enum UserStatus: int {
    /**
     * Estado indefinido ou não configurado.
     */
    case Undefined = 0;

    /**
     * Usuário ativo e habilitado para operações.
     */
    case Active = 1;

    /**
     * Usuário pendente de ativação ou aprovação.
     */
    case Pending = 2;

    /**
     * Usuário desativado ou bloqueado.
     */
    case Disabled = 3;
}