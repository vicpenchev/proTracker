<?php

namespace App\Filament\Resources;

use App\Enums\RuleFieldTypeEnum;
use App\Filament\Resources\RuleFieldResource\Pages;
use App\Filament\Resources\RuleFieldResource\RelationManagers\RulesRelationManager;
use App\Models\RuleField;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RuleFieldResource extends Resource
{
    protected static ?string $model = RuleField::class;

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationGroup = 'Transaction Import Rules';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->label('Type')
                    ->required()
                    ->live()
                    ->options(array_map(fn($value) => strtolower($value), RuleFieldTypeEnum::toArrayNames())),
                Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->sortable()
                    ->wrap()
                    ->limit(15)
                    ->tooltip(fn (string $state): string => $state)
                    ->searchable(isIndividual: true, isGlobal: false),
                Tables\Columns\TextColumn::make('type')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        RuleFieldTypeEnum::DATE->value => __('Date'),
                        RuleFieldTypeEnum::VALUE->value => __('Value'),
                        RuleFieldTypeEnum::TEXT->value => __('Text'),
                    }),
                Tables\Columns\TextColumn::make('description')
                    ->wrap()
                    ->limit(30)
                    ->searchable(isIndividual: true, isGlobal: false),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->hidden(function (RuleField $record) {
                        if($record->rules()->count() > 0){
                            return true;
                        }
                        return false;
                    }),
                Tables\Actions\Action::make('View Related Rules')
                    ->tooltip('You cannot Delete Rule Fields which has related rules to it!')
                    ->hidden(function (RuleField $record) {
                        if($record->rules()->count() > 0){
                            return false;
                        }
                        return true;
                    })
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->modalContent(
                        fn ($record) => view('filament.rule_fields.related_rules-modal', [
                            'record' => $record
                        ])
                    )
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->checkIfRecordIsSelectableUsing(
                function(RuleField $record) {
                    if($record->rules()->count() > 0){
                        return false;
                    }
                    return true;
                }
            );
    }

    public static function getRelations(): array
    {
        return [
            RulesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRuleFields::route('/'),
            'create' => Pages\CreateRuleField::route('/create'),
            'edit' => Pages\EditRuleField::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

}
