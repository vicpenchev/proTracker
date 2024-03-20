<?php

namespace App\Filament\CollectionFilterBuilder\Constraints\DateConstraint;

use App\Filament\CollectionFilterBuilder\Constraints\Operator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class IsBeforeOperator extends Operator
{
    /**
     * Apply the date filter to the given collection query based on the specified column.
     *
     * @param Collection $query The collection query to apply the filter on.
     * @param string $qualifiedColumn The qualified column name to filter on.
     *
     * @return Collection The filtered collection with date filter applied.
     */
    public function apply(Collection $query, string $qualifiedColumn) : Collection
    {
        $date = Carbon::parse($this->getSettings()['date']);

        return $query->filter(function ($item) use ($date, $qualifiedColumn) {
            $fieldVal = $this->castFieldValueToDate($item[$qualifiedColumn]);
            return ($this->isInverse() ? $fieldVal->gte($date) : $fieldVal->lte($date));
        });
    }

    /**
     * Casts the given field value to a Carbon date object.
     *
     * @param mixed $fieldVal The value to cast to a date.
     * @return null|Carbon Returns the cast date value as a Carbon object if successful, null otherwise.
     */
    private function castFieldValueToDate($fieldVal) : null | Carbon
    {
        if (blank($fieldVal)) {
            return null;
        }

        return Carbon::parse($fieldVal);
    }
}
