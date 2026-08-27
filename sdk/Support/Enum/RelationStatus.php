<?php

namespace Cerbero\Sdk\Support\Enum;

/**
 * Representa os possíveis estados de um relacionamento ou vínculo entre entidades no sistema.
 *
 * @package Cerbero\Sdk\Support\Enum
 */
enum RelationStatus: int {
    /**
     * Estado indefinido ou não configurado.
     */
    case Undefined = 0;

    /**
     * Relacionamento ativo e vigente.
     */
    case Active = 1;

//    case Pending = 2;

    /**
     * Relacionamento desativado ou revogado.
     */
    case Disabled = 3;
}