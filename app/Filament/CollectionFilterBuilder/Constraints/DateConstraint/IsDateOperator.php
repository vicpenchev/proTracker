<?php

namespace App\Filament\CollectionFilterBuilder\Constraints\DateConstraint;

use App\Filament\CollectionFilterBuilder\Constraints\Operator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class IsDateOperator extends Operator
{
    /**
     * Applies a filter to a collection based on the given qualified column and date.
     *
     * @param Collection $query The collection to apply the filter on.
     * @param string $qualifiedColumn The qualified column to filter on.
     * @return Collection Returns the filtered collection.
     */
    public function apply(Collection $query, string $qualifiedColumn) : Collection
    {
        $date = Carbon::parse($this->getSettings()['date']);

        return $query->filter(function ($item) use ($date, $qualifiedColumn) {
            $fieldVal = $this->castFieldValueToDate($item[$qualifiedColumn]);
            return ($this->isInverse() ? $fieldVal->diffInDays($date) != 0 : $fieldVal->diffInDays($date) == 0);
        });
    }

    /**
     * Casts a field value to a Date using Carbon library.
     *
     * @param mixed $fieldVal The value to cast to a Date.
     * @return null|Carbon Returns null if the $fieldVal is blank; otherwise, returns a Carbon instance representing the parsed date.
     */
    private function castFieldValueToDate($fieldVal) : null | Carbon
    {
        if (blank($fieldVal)) {
            return null;
        }

        return Carbon::parse($fieldVal);
    }
}
