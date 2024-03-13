<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RuleGroupResource\Pages;
use App\Filament\Resources\RuleGroupResource\RelationManagers;
use App\Models\RuleGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use App\Models\Rule;

class RuleGroupResource extends Resource
{
    protected static ?string $model = RuleGroup::class;

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationGroup = 'Transaction Import Rules';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Repeater::make('rules')
                    ->label('Rules')
                    ->helperText('Rules that will be applied to all records. The rules will be applied based on the order.')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('rule')
                            ->label('Rule')
                            ->options(Rule::query()->pluck('title', 'id'))
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->wrap()
                    ->limit(30)
                    ->searchable()
                    ->tooltip(fn (?string $state): ?string => $state),
                /*Tables\Columns\TextColumn::make('rules')
                    ->html(),*/
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListRuleGroups::route('/'),
            'create' => Pages\CreateRuleGroup::route('/create'),
            'edit' => Pages\EditRuleGroup::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
