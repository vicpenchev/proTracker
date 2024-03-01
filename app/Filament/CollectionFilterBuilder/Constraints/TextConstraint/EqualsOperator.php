<?php

namespace App\Filament\CollectionFilterBuilder\Constraints\TextConstraint;

use App\Filament\CollectionFilterBuilder\Constraints\Operator;
use Illuminate\Support\Collection;

class EqualsOperator extends Operator
{
    public function apply(Collection $query, string $qualifiedColumn) : Collection
    {
        $text = trim($this->getSettings()['text']);

        return $query->filter(function ($item) use ($text, $qualifiedColumn) {
            return ($this->isInverse() ? trim($item[$qualifiedColumn]) != $text : trim($item[$qualifiedColumn]) == $text);
        });
    }
}
