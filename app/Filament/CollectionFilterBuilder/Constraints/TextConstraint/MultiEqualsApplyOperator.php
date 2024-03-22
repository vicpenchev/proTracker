<?php

namespace App\Filament\CollectionFilterBuilder\Constraints\TextConstraint;

use App\Filament\CollectionFilterBuilder\Constraints\Operator;
use Illuminate\Support\Collection;

class MultiEqualsApplyOperator extends Operator
{
    /**
     * Applies a filter to the given collection based on the provided column value.
     *
     * @param Collection $query The collection to filter.
     * @param string $qualifiedColumn The column to filter on.
     *
     * @return Collection The filtered collection.
     */
    public function apply(Collection $query, string $qualifiedColumn) : Collection
    {
        $text = mb_strtolower(trim($this->getSettings()['text']));

        return $query->filter(function ($item) use ($text, $qualifiedColumn) {
            return ($this->isInverse() ? mb_strtolower(trim($item[$qualifiedColumn])) != $text : mb_strtolower(trim($item[$qualifiedColumn])) == $text);
        });
    }
}
