<?php

namespace App\Filament\CollectionFilterBuilder\Constraints\TextConstraint;

use App\Filament\CollectionFilterBuilder\Constraints\Operator;
use Illuminate\Support\Collection;

class EqualsApplyOperator extends Operator
{
    public function apply(Collection $query, string $qualifiedColumn) : Collection
    {
        $textSetting = $this->getSettings()['text'];

        if (is_array($textSetting)) {
            $resultQuery = $this->filterByMultipleCriteria($query, $textSetting, $qualifiedColumn);
        } else {
            $resultQuery = $this->filterByColumnValue($query, mb_strtolower(trim($textSetting)), $qualifiedColumn);
        }

        return $resultQuery;
    }

    private function filterByMultipleCriteria(Collection $query, array $textSetting, string $qualifiedColumn) : Collection
    {
        $resultQuery = Collection::make();

        foreach ($textSetting as $searchValue) {
            $searchValue = mb_strtolower(trim($searchValue));
            $resultQuery = $this->filterByColumnValue($query, $searchValue, $qualifiedColumn);

            if (($this->isInverse() && $resultQuery->isEmpty()) || (!$this->isInverse() && !$resultQuery->isEmpty())) {
                break;
            }
        }

        return $resultQuery;
    }

    private function filterByColumnValue(Collection $query, string $searchValue, string $qualifiedColumn) : Collection
    {
        return $query->filter(function ($item) use ($searchValue, $qualifiedColumn) {
            $itemValue = mb_strtolower(trim($item[$qualifiedColumn]));
            return $this->isInverse() ? $itemValue != $searchValue : $itemValue == $searchValue;
        });
    }
}
