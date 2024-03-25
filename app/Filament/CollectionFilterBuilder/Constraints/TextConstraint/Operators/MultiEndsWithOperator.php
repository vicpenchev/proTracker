<?php

namespace App\Filament\CollectionFilterBuilder\Constraints\TextConstraint\Operators;

use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint\Operators\EndsWithOperator;
use Filament\Forms\Components\TagsInput;

class MultiEndsWithOperator extends EndsWithOperator
{
    public function getSummary(): string
    {
        return __(
            $this->isInverse() ?
                'filament-tables::filters/query-builder.operators.text.ends_with.summary.inverse' :
                'filament-tables::filters/query-builder.operators.text.ends_with.summary.direct',
            [
                'attribute' => $this->getConstraint()->getAttributeLabel(),
                'text' => (is_array($this->getSettings()['text'])
                    ? (count($this->getSettings()['text']) > 1) ? '("' . implode('" OR "', $this->getSettings()['text']) . '")' : implode(' ', $this->getSettings()['text'])
                    : $this->getSettings()['text']),
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
