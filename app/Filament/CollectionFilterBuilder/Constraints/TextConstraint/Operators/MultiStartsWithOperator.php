<?php

namespace App\Filament\CollectionFilterBuilder\Constraints\TextConstraint\Operators;

use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint\Operators\StartsWithOperator;
use Filament\Forms\Components\TagsInput;

class MultiStartsWithOperator extends StartsWithOperator
{
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
