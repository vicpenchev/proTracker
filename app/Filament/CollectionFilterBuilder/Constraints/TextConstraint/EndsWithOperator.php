<?php

namespace App\Filament\CollectionFilterBuilder\Constraints\TextConstraint;

use App\Filament\CollectionFilterBuilder\Constraints\Operator;
use Illuminate\Support\Collection;

class EndsWithOperator extends Operator
{
    public function apply(Collection $query, string $qualifiedColumn) : Collection
    {
        $text = trim($this->getSettings()['text']);

        return $query->filter(function ($item) use ($text, $qualifiedColumn) {
            return ($this->isInverse() ? !(str_ends_with(trim($item[$qualifiedColumn]), $text)) : str_ends_with(trim($item[$qualifiedColumn]), $text));
        });
    }
}
