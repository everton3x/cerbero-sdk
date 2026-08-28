<?php

namespace Cerbero\Sdk\Support\Enum;

/**
 * Representa os possíveis estados de um perfil de usuário no sistema.
 *
 * @package Cerbero\Sdk\Support\Enum
 */
enum ProfileStatus: int
{
    /**
     * Estado indefinido ou não configurado.
     */
    case Undefined = 0;

    /**
     * Perfil ativo e válido.
     */
    case Active = 1;

//    case Pending = 2;

    /**
     * Perfil desativado.
     */
    case Disabled = 3;
}
