<?php

namespace App\Filament\CollectionFilterBuilder\Constraints\NumberConstraint;

use App\Filament\CollectionFilterBuilder\Constraints\Operator;
use Illuminate\Support\Collection;

class IsMinOperator extends Operator
{
    public function apply(Collection $query, string $qualifiedColumn) : Collection
    {
        $number = floatval($this->getSettings()['number']);

        return $query->filter(function ($item) use ($number, $qualifiedColumn) {
            $fieldVal = $this->castFieldValueToNumber($item[$qualifiedColumn]);
            $compare = $number - $fieldVal;
            return ($this->isInverse() ? $compare > 0 : $compare <= 0);
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
