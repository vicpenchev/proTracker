<?php

namespace App\Filament\CollectionFilterBuilder\Constraints\NumberConstraint;

use App\Filament\CollectionFilterBuilder\Constraints\Operator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class EqualsOperator extends Operator
{
    public function apply(Collection $query, string $qualifiedColumn) : Collection
    {
        $number = floatval($this->getSettings()['number']);

        return $query->filter(function ($item) use ($number, $qualifiedColumn) {
            $fieldVal = $this->castFieldValueToNumber($item[$qualifiedColumn]);
            $epsilon = 0.00001;
            return ($this->isInverse() ? abs($number-$fieldVal) > $epsilon : abs($number-$fieldVal) < $epsilon);
        });
    }

    private function castFieldValueToNumber($fieldVal, $precision = 2) : null | float
    {
        if (blank($fieldVal)) {
            return null;
        }

        $fieldVal = preg_replace('/[^0-9.]/', '', $fieldVal);
        $fieldVal = floatval($fieldVal);

        return round($fieldVal, $precision);
    }
}
