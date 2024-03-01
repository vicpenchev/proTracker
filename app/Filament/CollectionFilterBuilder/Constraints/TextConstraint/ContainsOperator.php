<?php

namespace App\Filament\CollectionFilterBuilder\Constraints\TextConstraint;

use App\Filament\CollectionFilterBuilder\Constraints\Operator;
use Illuminate\Support\Collection;

class ContainsOperator extends Operator
{
    public function apply(Collection $query, string $qualifiedColumn) : Collection
    {
        $text = trim($this->getSettings()['text']);

        return $query->filter(function ($item) use ($text, $qualifiedColumn) {
            return ($this->isInverse() ? !(str_contains(trim($item[$qualifiedColumn]), $text)) : str_contains(trim($item[$qualifiedColumn]), $text));
        });
    }
}
