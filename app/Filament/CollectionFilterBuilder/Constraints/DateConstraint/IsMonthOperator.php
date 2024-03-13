<?php

namespace App\Filament\CollectionFilterBuilder\Constraints\DateConstraint;

use App\Filament\CollectionFilterBuilder\Constraints\Operator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class IsMonthOperator extends Operator
{
    public function apply(Collection $query, string $qualifiedColumn) : Collection
    {
        $month = $this->getSettings()['month'];

        return $query->filter(function ($item) use ($month, $qualifiedColumn) {
            $fieldVal = $this->castFieldValueToNumber($item[$qualifiedColumn]);
            return ($this->isInverse() ? $fieldVal->format('m') != $month : $fieldVal->format('m') == $month);
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
