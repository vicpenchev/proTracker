<?php

namespace App\Filament\CollectionFilterBuilder\Constraints\DateConstraint;

use App\Filament\CollectionFilterBuilder\Constraints\Operator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class IsAfterOperator extends Operator
{
    /**
     * Applies the filter to the given collection.
     *
     * @param Collection $query The collection to apply the filter to.
     * @param string $qualifiedColumn The qualified column name to filter on.
     * @return Collection The filtered collection.
     */
    public function apply(Collection $query, string $qualifiedColumn) : Collection
    {
        $date = Carbon::parse($this->getSettings()['date']);

        return $query->filter(function ($item) use ($date, $qualifiedColumn) {
            $fieldVal = $this->castFieldValueToDate($item[$qualifiedColumn]);
            return ($this->isInverse() ? $fieldVal->lte($date) : $fieldVal->gte($date));
        });
    }

    /**
     * Casts a field value to a Carbon date object.
     *
     * @param mixed $fieldVal The value to cast to a date.
     * @return null|Carbon A Carbon date object if the provided field value is not blank, otherwise returns null.
     */
    private function castFieldValueToDate($fieldVal) : null | Carbon
    {
        if (blank($fieldVal)) {
            return null;
        }

        return Carbon::parse($fieldVal);
    }
}
