<?php

namespace App\Filament\Resources;

use App\Enums\RuleFieldTypeEnum;
use App\Enums\RuleTypeEnum;
use App\Enums\TransactionTypeEnum;
use App\Filament\CollectionFilterBuilder\Constraints\TextConstraint\Operators\MultiContainsOperator;
use App\Filament\CollectionFilterBuilder\Constraints\TextConstraint\Operators\MultiEndsWithOperator;
use App\Filament\CollectionFilterBuilder\Constraints\TextConstraint\Operators\MultiEqualsOperator;
use App\Filament\CollectionFilterBuilder\Constraints\TextConstraint\Operators\MultiStartsWithOperator;
use App\Filament\Resources\RuleResource\Pages;
use App\Filament\Resources\RuleResource\RelationManagers\RuleGroupsRelationManager;
use App\Models\Category;
use App\Models\Rule;
use App\Models\RuleField;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use App\Filament\CollectionFilterBuilder\Constraints\TextConstraint;
use Filament\Tables\Filters\QueryBuilder\Forms\Components\RuleBuilder;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class RuleResource extends Resource
{
    protected static ?string $model = Rule::class;

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationGroup = 'Transaction Import Rules';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Rule Settings')
                        ->description('General Rule Settings')
                        ->schema([
                            TextInput::make('title')
                                ->required()
                                ->maxLength(255),
                            Select::make('type')
                                ->label('Type')
                                ->required()
                                ->live()
                                ->options(array_map(fn($value) => strtolower($value), RuleTypeEnum::toArrayNames())),
                            Select::make('transaction_type')
                                ->label('Transaction Type')
                                ->helperText('Set Transaction Type to this value if the rules are true')
                                ->visible(fn (Forms\Get $get): bool => ($get('type') == RuleTypeEnum::TRANSACTION_TYPE->value))
                                ->required(fn (Forms\Get $get): bool => ($get('type') == RuleTypeEnum::TRANSACTION_TYPE->value))
                                ->options(array_map(fn($value) => strtolower($value), TransactionTypeEnum::toArrayNames())),
                            Select::make('category_id')
                                ->label('Transaction Category')
                                ->helperText('Set Transaction Category to this value if the rules are true')
                                ->visible(fn (Forms\Get $get): bool => ($get('type') == RuleTypeEnum::TRANSACTION_CATEGORY->value))
                                ->required(fn (Forms\Get $get): bool => ($get('type') == RuleTypeEnum::TRANSACTION_CATEGORY->value))
                                ->options(Category::query()->pluck('title', 'id')),
                            Forms\Components\TagsInput::make('merge_fields')
                                ->label('Merge Fields')
                                ->helperText('Merge fields data into the Notes column if the rules are true. Data will be separated by ";"')
                                ->visible(fn (Forms\Get $get): bool => ($get('type') == RuleTypeEnum::TRANSACTION_COMBINE->value))
                                ->required(fn (Forms\Get $get): bool => ($get('type') == RuleTypeEnum::TRANSACTION_COMBINE->value)),
                            Select::make('rule_fields')
                                ->label('Rule Fields')
                                ->relationship('rule_fields', 'title')
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function ($state) {
                                    Session::put('rule_fields', $state);
                                })
                                ->afterStateHydrated(function ($state) {
                                    Session::put('rule_fields', $state);
                                })
                                ->multiple()
                                //->options(RuleField::query()->pluck('title', 'id'))
                                ->visible(fn (Forms\Get $get): bool => in_array($get('type'), RuleTypeEnum::toArray()))
                        ])
                        ->columns(2),
                    Forms\Components\Wizard\Step::make('Set Rules')
                        ->description('Rules which will be applied to the original CSV file')
                        ->schema([
                            Forms\Components\Placeholder::make('test')
                                ->content('If you don\'t select Rule Fields into the previous step then the conditions will be always true and the rule will be applied to all records.')
                                ->visible(fn (Forms\Get $get): bool => !((bool)$get('rule_fields'))),
                            RuleBuilder::make('rules')
                                ->visible(fn (Forms\Get $get): bool => (bool)$get('rule_fields'))
                                ->columnSpanFull()
                                ->constraints(self::generateAvailableRuleConditions()),
                                /*->constraints(fn (Forms\Get $get): array => match ($get('rule_fields')) {
                                    '1' => [DateConstraint::make('date')->label('Date')],
                                    default => [],
                                }),*/
                        ]),
                ])
                ->columnSpanFull()
                ->submitAction(new HtmlString(Blade::render(<<<BLADE
                <x-filament::button
                    type="submit"
                    size="sm"
                >
                    Save
                </x-filament::button>
            BLADE))),
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
                Tables\Actions\DeleteAction::make()
                    ->hidden(function (Rule $record) {
                        if($record->rule_groups()->count() > 0){
                            return true;
                        }
                        return false;
                    }),
                Tables\Actions\Action::make('View Related Rule Groups')
                    ->tooltip('You cannot Delete Rule which has related Rule Groups to it!')
                    ->hidden(function (Rule $record) {
                        if($record->rule_groups()->count() > 0){
                            return false;
                        }
                        return true;
                    })
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->modalContent(
                        fn ($record) => view('filament.rules.related_rule_groups-modal', [
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
                function(Rule $record) {
                    if($record->rule_groups()->count() > 0){
                        return false;
                    }
                    return true;
                }
            );
    }

    public static function getRelations(): array
    {
        return [
            RuleGroupsRelationManager::class,
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

    private static function generateAvailableRuleConditions(): array
    {
        //Session::remove('rule_fields');
        if(!Session::has('rule_fields') || !count(Session::get('rule_fields'))) {
            return [];
        }

        $rule_field_objects = [];
        $selected_rule_fields = array_values(Session::get('rule_fields'));
        $rule_fields_data = RuleField::query()->whereIn('id', $selected_rule_fields)->get(['title', 'type']);

        foreach ($rule_fields_data as $field_data) {
            switch ($field_data->type) {
                case RuleFieldTypeEnum::DATE->value:
                    $rule_field_objects[] = DateConstraint::make(Str::slug($field_data->title))->label($field_data->title);
                    break;
                case RuleFieldTypeEnum::VALUE->value:
                    $rule_field_objects[] = NumberConstraint::make(Str::slug($field_data->title))->label($field_data->title);
                    break;
                case RuleFieldTypeEnum::TEXT->value:
                    $rule_field_objects[] = TextConstraint::make(Str::slug($field_data->title))
                        ->label($field_data->title)
                        ->icon(FilamentIcon::resolve('tables::filters.query-builder.constraints.text') ?? 'heroicon-m-language')
                        ->operators([
                            MultiContainsOperator::class,
                            MultiEndsWithOperator::class,
                            MultiEqualsOperator::class,
                            MultiStartsWithOperator::class,
                        ]);
                    break;
            }
        }
        return $rule_field_objects;
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
