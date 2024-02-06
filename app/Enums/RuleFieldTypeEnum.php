<?php

namespace App\Enums;

use App\Traits\EnumsTrait;

enum RuleFieldTypeEnum: int
{
    use EnumsTrait;

    case DATE = 1;
    case VALUE = 2;
    case TEXT = 3;
}
