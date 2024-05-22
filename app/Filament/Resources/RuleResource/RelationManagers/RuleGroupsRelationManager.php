<?php

namespace App\Filament\Resources\RuleResource\RelationManagers;

use App\Filament\Resources\RuleGroupResource;
use App\Models\RuleGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class RuleGroupsRelationManager extends RelationManager
{
    protected static string $relationship = 'rule_groups';

    protected static ?string $recordTitleAttribute = 'reference';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
            ])
            ->filters([])
            ->headerActions([
                //Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->url(function (RuleGroup $record, RuleGroupResource $resource) {
                        return $resource::getUrl('edit', ['record' => $record]);
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->groupedBulkActions([
                //Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

}
