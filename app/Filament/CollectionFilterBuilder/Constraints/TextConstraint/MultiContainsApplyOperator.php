<?php

namespace App\Filament\CollectionFilterBuilder\Constraints\TextConstraint;

use App\Filament\CollectionFilterBuilder\Constraints\Operator;
use Illuminate\Support\Collection;

class MultiContainsApplyOperator extends Operator
{
    /**
     * Applies a text filter to a collection query.
     *
     * @param Collection $query The collection query to filter.
     * @param string $qualifiedColumn The column name to filter on.
     * @return Collection The filtered collection query.
     */
    public function apply(Collection $query, string $qualifiedColumn) : Collection
    {
        $text = mb_strtolower(trim($this->getSettings()['text']));

        return $query->filter(function ($item) use ($text, $qualifiedColumn) {
            return ($this->isInverse() ? !(str_contains(mb_strtolower(trim($item[$qualifiedColumn])), $text)) : str_contains(mb_strtolower(trim($item[$qualifiedColumn])), $text));
        });
    }
}
