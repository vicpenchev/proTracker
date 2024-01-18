<?php

namespace App\Enums;

use App\Traits\EnumsTrait;

enum TransactionCreateTypeEnum: int
{
    use EnumsTrait;

    case MANUAL = 1;
    case IMPORTED = 2;
}
