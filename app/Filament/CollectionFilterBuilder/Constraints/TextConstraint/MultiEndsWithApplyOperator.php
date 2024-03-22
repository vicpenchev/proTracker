<?php

namespace App\Filament\CollectionFilterBuilder\Constraints\TextConstraint;

use App\Filament\CollectionFilterBuilder\Constraints\Operator;
use Illuminate\Support\Collection;

class MultiEndsWithApplyOperator extends Operator
{
    /**
     * Applies a filter to the given Collection based on the specified qualified column.
     *
     * @param Collection $query The Collection to apply the filter on.
     * @param string $qualifiedColumn The qualified column to filter on.
     * @return Collection The filtered Collection.
     */
    public function apply(Collection $query, string $qualifiedColumn) : Collection
    {
        $text = mb_strtolower(trim($this->getSettings()['text']));

        return $query->filter(function ($item) use ($text, $qualifiedColumn) {
            return ($this->isInverse() ? !(str_ends_with(mb_strtolower(trim($item[$qualifiedColumn])), $text)) : str_ends_with(mb_strtolower(trim($item[$qualifiedColumn])), $text));
        });
    }
}
