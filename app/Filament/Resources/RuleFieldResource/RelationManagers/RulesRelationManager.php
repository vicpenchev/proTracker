<?php

namespace App\Filament\Resources\RuleFieldResource\RelationManagers;

use App\Enums\RuleTypeEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class RulesRelationManager extends RelationManager
{
    protected static string $relationship = 'rules';

    protected static ?string $recordTitleAttribute = 'reference';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        RuleTypeEnum::TRANSACTION_TYPE->value => __('Type'),
                        RuleTypeEnum::TRANSACTION_CATEGORY->value => __('Category'),
                        RuleTypeEnum::TRANSACTION_COMBINE->value => __('Combination'),
                    })
                    ->icon(fn (int $state): string => match ($state) {
                        RuleTypeEnum::TRANSACTION_TYPE->value => 'heroicon-o-banknotes',
                        RuleTypeEnum::TRANSACTION_CATEGORY->value => 'heroicon-o-tag',
                        RuleTypeEnum::TRANSACTION_COMBINE->value => 'heroicon-o-plus',
                    })
                    ->iconPosition('after')
                    ->color(fn (int $state): string => match ($state) {
                        RuleTypeEnum::TRANSACTION_TYPE->value => 'danger',
                        RuleTypeEnum::TRANSACTION_CATEGORY->value => 'success',
                        RuleTypeEnum::TRANSACTION_COMBINE->value => 'warning',
                    }),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->groupedBulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

}
