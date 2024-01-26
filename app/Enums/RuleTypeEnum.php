<?php

namespace App\Enums;

use App\Traits\EnumsTrait;

enum RuleTypeEnum: int
{
    use EnumsTrait;

    case TRANSACTION_TYPE = 1;
    case TRANSACTION_CATEGORY = 2;
    case TRANSACTION_COMBINE = 3;
}
