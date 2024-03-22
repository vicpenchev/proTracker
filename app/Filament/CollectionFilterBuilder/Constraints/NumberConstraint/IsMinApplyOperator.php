<?php

namespace App\Filament\CollectionFilterBuilder\Constraints\NumberConstraint;

use App\Filament\CollectionFilterBuilder\Constraints\Operator;
use Illuminate\Support\Collection;

class IsMinApplyOperator extends Operator
{
    /**
     * Applies a filter to the given Collection based on the qualified column value.
     *
     * @param Collection $query The Collection to apply the filter on.
     * @param string $qualifiedColumn The qualified column to filter on.
     * @return Collection The filtered Collection.
     */
    public function apply(Collection $query, string $qualifiedColumn) : Collection
    {
        $number = floatval($this->getSettings()['number']);

        return $query->filter(function ($item) use ($number, $qualifiedColumn) {
            $fieldVal = $this->castFieldValueToNumber($item[$qualifiedColumn]);
            $compare = $number - $fieldVal;
            return ($this->isInverse() ? $compare > 0 : $compare <= 0);
        });
    }

    /**
     * Casts the given field value to a number with specified precision.
     *
     * @param mixed $fieldVal The field value to be cast to a number.
     * @param int $precision The precision of the resulting number. Defaults to 2 if not provided.
     *
     * @return null|float Returns the cast field value as a number, rounded to the specified precision.
     * Returns null if the field value is blank or cannot be cast to a number.
     */
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
