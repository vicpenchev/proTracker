<?php

namespace App\Filament\CollectionFilterBuilder\Constraints\TextConstraint;

use App\Filament\CollectionFilterBuilder\Constraints\Operator;
use Illuminate\Support\Collection;

class MultiStartsWithApplyOperator extends Operator
{
    /**
     * Apply a filter to the given collection.
     *
     * @param Collection $query The collection to apply the filter to.
     * @param string $qualifiedColumn The qualified column name used for comparison.
     * @return Collection The filtered collection.
     */
    public function apply(Collection $query, string $qualifiedColumn) : Collection
    {
        $text = mb_strtolower(trim($this->getSettings()['text']));

        return $query->filter(function ($item) use ($text, $qualifiedColumn) {
            return ($this->isInverse() ? !(str_starts_with(mb_strtolower(trim($item[$qualifiedColumn])), $text)) : str_starts_with(mb_strtolower(trim($item[$qualifiedColumn])), $text));
        });
    }
}
