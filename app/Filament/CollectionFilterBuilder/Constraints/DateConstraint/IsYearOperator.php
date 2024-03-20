<?php

namespace App\Filament\CollectionFilterBuilder\Constraints\DateConstraint;

use App\Filament\CollectionFilterBuilder\Constraints\Operator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class IsYearOperator extends Operator
{
    /**
     * Applies the year filter to the given collection query.
     *
     * @param Collection $query The collection query to apply the filter to.
     * @param string $qualifiedColumn The qualified column name to filter by.
     * @return Collection Returns a filtered collection based on the year filter.
     */
    public function apply(Collection $query, string $qualifiedColumn) : Collection
    {
        $year = $this->getSettings()['year'];

        return $query->filter(function ($item) use ($year, $qualifiedColumn) {
            $fieldVal = $this->castFieldValueToDate($item[$qualifiedColumn]);
            return ($this->isInverse() ? $fieldVal->format('Y') != $year : $fieldVal->format('Y') == $year);
        });
    }

    /**
     * Casts the given field value to a Carbon date object.
     *
     * @param mixed $fieldVal The value to be cast to a date object.
     * @return null|Carbon Returns a Carbon date object if the field value is not blank, otherwise returns null.
     */
    private function castFieldValueToDate($fieldVal) : null | Carbon
    {
        if (blank($fieldVal)) {
            return null;
        }

        return Carbon::parse($fieldVal);
    }
}
