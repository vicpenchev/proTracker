<?php

namespace App\Filament\CollectionFilterBuilder\Constraints\TextConstraint\Operators;

use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint\Operators\ContainsOperator;
use Filament\Forms\Components\TagsInput;

class MultiContainsOperator extends ContainsOperator
{

    public function getSummary(): string
    {
        return __(
            $this->isInverse() ?
                'filament-tables::filters/query-builder.operators.text.contains.summary.inverse' :
                'filament-tables::filters/query-builder.operators.text.contains.summary.direct',
            [
                'attribute' => $this->getConstraint()->getAttributeLabel(),
                'text' => '',
            ],
        );
    }

    public function getFormSchema(): array
    {
        return [
            TagsInput::make('text')
                ->label('String List')
                ->placeholder('Text')
                //->label(__('filament-tables::filters/query-builder.operators.text.form.text.label'))
                ->required()
                ->columnSpanFull(),
        ];
    }
}
