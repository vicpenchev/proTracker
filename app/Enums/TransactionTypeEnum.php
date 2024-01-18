<?php

namespace App\Enums;

use App\Traits\EnumsTrait;

enum TransactionTypeEnum: int
{
    use EnumsTrait;

    case EXPENSE = 1;
    case INCOME = 2;
}
