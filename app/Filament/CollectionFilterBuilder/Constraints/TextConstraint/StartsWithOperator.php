<?php

namespace App\Filament\CollectionFilterBuilder\Constraints\TextConstraint;

use App\Filament\CollectionFilterBuilder\Constraints\Operator;
use Illuminate\Support\Collection;

class StartsWithOperator extends Operator
{
    public function apply(Collection $query, string $qualifiedColumn) : Collection
    {
        $text = trim($this->getSettings()['text']);

        return $query->filter(function ($item) use ($text, $qualifiedColumn) {
            return ($this->isInverse() ? !(str_starts_with(trim($item[$qualifiedColumn]), $text)) : str_starts_with(trim($item[$qualifiedColumn]), $text));
        });
    }
}
