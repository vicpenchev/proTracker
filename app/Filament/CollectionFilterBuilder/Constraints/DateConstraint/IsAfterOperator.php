<?php

namespace App\Filament\CollectionFilterBuilder\Constraints\DateConstraint;

use App\Filament\CollectionFilterBuilder\Constraints\Operator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class IsAfterOperator extends Operator
{
    public function apply(Collection $query, string $qualifiedColumn) : Collection
    {
        $date = Carbon::parse($this->getSettings()['date']);

        return $query->filter(function ($item) use ($date, $qualifiedColumn) {
            $fieldVal = $this->castFieldValueToNumber($item[$qualifiedColumn]);
            return ($this->isInverse() ? $fieldVal->lte($date) : $fieldVal->gte($date));
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
