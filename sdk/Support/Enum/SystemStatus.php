<?php

namespace Cerbero\Sdk\Support\Enum;

/**
 * Representa os possíveis estados de um sistema cadastrado na plataforma.
 *
 * @package Cerbero\Sdk\Support\Enum
 */
enum SystemStatus: int
{
    /**
     * Estado indefinido ou não configurado.
     */
    case Undefined = 0;

    /**
     * Sistema ativo e operacional.
     */
    case Active = 1;

//    case Pending = 2;

    /**
     * Sistema desativado.
     */
    case Disabled = 3;
}
