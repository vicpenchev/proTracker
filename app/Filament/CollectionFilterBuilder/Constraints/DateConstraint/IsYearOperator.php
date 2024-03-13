<?php

namespace App\Filament\CollectionFilterBuilder\Constraints\DateConstraint;

use App\Filament\CollectionFilterBuilder\Constraints\Operator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class IsYearOperator extends Operator
{
    public function apply(Collection $query, string $qualifiedColumn) : Collection
    {
        $year = $this->getSettings()['year'];

        return $query->filter(function ($item) use ($year, $qualifiedColumn) {
            $fieldVal = $this->castFieldValueToNumber($item[$qualifiedColumn]);
            return ($this->isInverse() ? $fieldVal->format('Y') != $year : $fieldVal->format('Y') == $year);
        });
    }

    private function castFieldValueToNumber($fieldVal) : null | Carbon
    {
        if (blank($fieldVal)) {
            return null;
        }

        return Carbon::parse($fieldVal);
    }
}
