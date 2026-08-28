<?php

namespace Cerbero\Sdk\Support\Enum;

/**
 * Representa os possíveis estados de uma permissão no sistema.
 *
 * @package Cerbero\Sdk\Support\Enum
 */
enum PermissionStatus: int
{
    /**
     * Estado indefinido ou não configurado.
     */
    case Undefined = 0;

    /**
     * Permissão ativa e válida.
     */
    case Active = 1;

//    case Pending = 2;

    /**
     * Permissão desativada ou revogada.
     */
    case Disabled = 3;
}
