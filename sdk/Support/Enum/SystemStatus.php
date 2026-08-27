<?php

namespace Cerbero\Sdk\Support\Enum;

enum SystemStatus: int {
    case Undefined = 0;
    case Active = 1;
//    case Pending = 2;
    case Disabled = 3;
}