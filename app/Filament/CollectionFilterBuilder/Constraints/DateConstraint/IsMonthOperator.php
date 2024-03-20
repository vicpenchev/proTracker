<?php

namespace App\Filament\CollectionFilterBuilder\Constraints\DateConstraint;

use App\Filament\CollectionFilterBuilder\Constraints\Operator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class IsMonthOperator extends Operator
{
    /**
     * Applies a filter to the given collection based on the specified month and column.
     *
     * @param Collection $query The collection to apply the filter to.
     * @param string $qualifiedColumn The name of the column to filter on.
     * @return Collection The filtered collection.
     */
    public function apply(Collection $query, string $qualifiedColumn) : Collection
    {
        $month = $this->getSettings()['month'];

        return $query->filter(function ($item) use ($month, $qualifiedColumn) {
            $fieldVal = $this->castFieldValueToDate($item[$qualifiedColumn]);
            return ($this->isInverse() ? $fieldVal->format('m') != $month : $fieldVal->format('m') == $month);
        });
    }

    /**
     * Casts a field value to a date.
     *
     * @param mixed $fieldVal The field value to be cast.
     * @return null|Carbon An instance of Carbon if the field value is not blank, otherwise null.
     */
    private function castFieldValueToDate($fieldVal) : null | Carbon
    {
        if (blank($fieldVal)) {
            return null;
        }

        return Carbon::parse($fieldVal);
    }
}
