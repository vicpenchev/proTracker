<?php

namespace App\Filament\Resources;

use App\Enums\RuleTypeEnum;
use App\Enums\TransactionTypeEnum;
use App\Filament\Resources\RuleResource\Pages;
use App\Filament\Resources\RuleResource\RelationManagers;
use App\Models\Rule;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\QueryBuilder\Constraints\SelectConstraint;
use Filament\Tables\Filters\QueryBuilder\Forms\Components\RuleBuilder;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;

class RuleResource extends Resource
{
    protected static ?string $model = Rule::class;

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
                    ->options(array_map(fn($value) => strtolower($value), RuleTypeEnum::toArrayNames())),
                RuleBuilder::make('rules')
                    ->columnSpanFull()
                    ->label('11111')
                    ->constraints([
                        SelectConstraint::make('type')
                            ->label('Transaction Type')
                            ->options(array_map(fn($value) => strtolower($value), TransactionTypeEnum::toArrayNames())),
                    ]),
                    /*->afterStateUpdated(function ($state) {
                        json_encode($state);
                    }),*/
                //->blockPickerColumns($filter->getConstraintPickerColumns())
                //->blockPickerWidth($filter->getConstraintPickerWidth())
                //->live(onBlur: true),
            ]);
    }

    public static function table(Table $table): Table
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
                        RuleTypeEnum::TRANSACTION_TYPE->value => 'heroicon-o-arrow-down-circle',
                        RuleTypeEnum::TRANSACTION_CATEGORY->value => 'heroicon-o-arrow-down-circle',
                        RuleTypeEnum::TRANSACTION_COMBINE->value => 'heroicon-o-arrow-down-circle',
                    })
                    ->iconPosition('after')
                    ->color(fn (int $state): string => match ($state) {
                        RuleTypeEnum::TRANSACTION_TYPE->value => 'danger',
                        RuleTypeEnum::TRANSACTION_CATEGORY->value => 'success',
                        RuleTypeEnum::TRANSACTION_COMBINE->value => 'warning',
                    }),
                /*Tables\Columns\TextColumn::make('rules')
                    ->wrap()
                    ->limit(30)
                    ->tooltip(fn (?string $state): ?string => $state),*/
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRules::route('/'),
            'create' => Pages\CreateRule::route('/create'),
            'edit' => Pages\EditRule::route('/{record}/edit'),
        ];
    }
}
