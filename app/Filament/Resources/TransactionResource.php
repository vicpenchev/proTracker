<?php

namespace App\Filament\Resources;

use App\Enums\TransactionCreateTypeEnum;
use App\Enums\TransactionTypeEnum;
use App\Filament\Imports\TransactionImporter;
use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Category;
use App\Models\Transaction;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Filament\Tables\Enums\FiltersLayout;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function form(Form $form): Form
    {
        return $form->schema(Transaction::getForm());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('account.title')
                    ->sortable()
                    ->formatStateUsing(function(string $state) {
                        $string = Str::replaceMatches('/[^A-Za-z0-9\s]++/', '', $state);
                        return Str::of($string)
                            ->explode(' ')
                            ->map(fn($part) => Str::of($part)->substr(0, 1)->upper())
                            ->join('');
                    })
                    ->wrap(5)
                    ->tooltip(fn (string $state): string => $state),
                Tables\Columns\TextColumn::make('type')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        TransactionTypeEnum::EXPENSE->value => __('Expense'),
                        TransactionTypeEnum::INCOME->value => __('Income'),
                    })
                    ->icon(fn (int $state): string => match ($state) {
                            TransactionTypeEnum::EXPENSE->value => 'heroicon-o-arrow-down-circle',
                            TransactionTypeEnum::INCOME->value => 'heroicon-o-arrow-up-circle',
                    })
                    ->iconPosition('after')
                    ->color(fn (int $state): string => match ($state) {
                        TransactionTypeEnum::EXPENSE->value => 'danger',
                        TransactionTypeEnum::INCOME->value => 'success',
                    }),
                Tables\Columns\TextColumn::make('create_type')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('import_id')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('category.title')
                    ->sortable()
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->wrap()
                    ->limit(20)
                    ->tooltip(fn (?string $state): ?string => $state),
                Tables\Columns\TextColumn::make('value')
                    ->label('Sum')
                    ->numeric()
                    ->suffix(function(Transaction $record) {
                        if($record->currency()->prefix) {
                            return '';
                        } else {
                            return ' ' . ($record->currency()->symbol ?? '');
                        }
                    })
                    ->prefix(function(Transaction $record) {
                        if($record->currency()->prefix) {
                            return ($record->currency()->symbol ?? '') . ' ';
                        } else {
                            return '';
                        }
                    })
                    ->html()
                    ->sortable()
                    ->summarize([
                        //Tables\Columns\Summarizers\Sum::make(),
                        //Tables\Columns\Summarizers\Range::make(),
                        //Tables\Columns\Summarizers\Average::make()
                    ]),
                    /*->summarize(Tables\Columns\Summarizers\Sum::make()
                        ->label('Expenses')
                        ->query(fn (\Illuminate\Database\Query\Builder $query) => $query->where('category_id', 1))
                    )
                    ->summarize(Tables\Columns\Summarizers\Sum::make()
                        ->label('Income')
                        ->query(fn (\Illuminate\Database\Query\Builder $query) => $query->where('category_id', 2))
                    ),*/
                Tables\Columns\TextColumn::make('date')
                    ->dateTime()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => Carbon::parse($state)->format('Y-m-d'))
                    ->tooltip(fn (string $state): string => $state),
                Tables\Columns\TextColumn::make('from_acc')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->wrap()
                    ->limit(10)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip(fn (?string $state): ?string => $state),
                Tables\Columns\TextColumn::make('to_acc')
                    ->wrap()
                    ->limit(10)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->tooltip(fn (?string $state): ?string => $state),
                Tables\Columns\TextColumn::make('notes')
                    ->wrap()
                    ->limit(30)
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->tooltip(fn (?string $state): ?string => $state),
                Tables\Columns\IconColumn::make('published')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => Carbon::parse($state)->format('Y-m-d'))
                    ->tooltip(fn (string $state): string => $state)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn (string $state): string => Carbon::parse($state)->format('Y-m-d'))
                    ->tooltip(fn (string $state): string => $state),
            ])
            ->defaultSort('date', 'desc')
            ->paginated([10, 25, 50, 100, 500])
            ->defaultPaginationPageOption(25)
            ->deferLoading()
            ->persistFiltersInSession()
            /*->filtersTriggerAction(
                fn (Tables\Actions\Action $action) => $action
                    ->button()
                    ->label(__('Filter')),
            )*/
            ->filters([
                Tables\Filters\SelectFilter::make('account')
                    ->label('Account')
                    ->relationship('account', 'title')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Transaction Type')
                    ->options(array_map(fn($value) => strtolower($value), TransactionTypeEnum::toArrayNames())),
                Tables\Filters\SelectFilter::make('category')
                    ->label('Category')
                    ->relationship('category', 'title')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from_date')
                            ->label('From Date')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->live()
                            ->maxDate(function (Forms\Get $get) {
                                if($get('to_date')) {
                                    return $get('to_date');
                                }
                                return null;
                            }),
                        Forms\Components\DatePicker::make('to_date')
                            ->label('To Date')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->live()
                            ->minDate(function (Forms\Get $get) {
                                if($get('from_date')) {
                                    return $get('from_date');
                                }
                                return null;
                            }),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['to_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from_date'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('From:  ' . Carbon::parse($data['from_date'])->toFormattedDateString())
                                ->removeField('from_date');
                        }

                        if ($data['to_date'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('Until: ' . Carbon::parse($data['to_date'])->toFormattedDateString())
                                ->removeField('to_date');
                        }

                        return $indicators;
                    }),
                Tables\Filters\Filter::make('sum')
                    ->form([
                        Forms\Components\TextInput::make('from_value')
                            ->label('Min Sum')
                            ->numeric()
                            ->inputMode('decimal'),
                        Forms\Components\TextInput::make('to_value')
                            ->label('Max Sum')
                            ->numeric()
                            ->inputMode('decimal'),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from_value'],
                                fn (Builder $query, $date): Builder => $query->where('value', '>=', $date),
                            )
                            ->when(
                                $data['to_value'],
                                fn (Builder $query, $date): Builder => $query->where('value', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from_value'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('Min Sum:  ' . $data['from_value'])
                                ->removeField('from_value');
                        }

                        if ($data['to_value'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('Max Sum: ' . $data['to_value'])
                                ->removeField('to_value');
                        }

                        return $indicators;
                    }),
                Tables\Filters\SelectFilter::make('create_type')
                    ->label('Create Type')
                    ->options(array_map(fn($value) => strtolower($value), TransactionCreateTypeEnum::toArrayNames())),
                Tables\Filters\Filter::make('from_account')
                    ->form([
                        Forms\Components\TextInput::make('from_acc')
                            ->label('From Account')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('to_acc')
                            ->label('To Account')
                            ->maxLength(255),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from_acc'],
                                fn (Builder $query, $from_acc): Builder => $query->where('from_acc', 'like', '%' . $from_acc . '%'),
                            )
                            ->when(
                                $data['to_acc'],
                                fn (Builder $query, $to_acc): Builder => $query->where('to_acc', 'like', '%' . $to_acc . '%'),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from_acc'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('From Account: ' . $data['from_acc'])
                                ->removeField('from_acc');
                        }

                        if ($data['to_acc'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('To Account: ' . $data['to_acc'])
                                ->removeField('to_acc');
                        }

                        return $indicators;
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(2)
            /*->deferFilters()
            ->filtersTriggerAction(
                fn (Tables\Actions\Action $action) => $action
                    ->button()
                    ->slideOver()
                    ->label(__('Filter')),
            )
            ->filtersApplyAction(
                fn (Tables\Actions\Action $action) => $action
                    ->button()
                    ->label(__('Filter')),
            )*/
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->requiresConfirmation(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('publish')
                        ->visible(function (Transaction $record) {
                            return !$record->published;
                        })
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Transaction $record) {
                            $record->publish();
                        })
                        ->after(function (Transaction $record){
                            Notification::make()->success()->title('Transaction published successfully')
                                ->duration(2000)
                                ->body('Transaction ' . $record->id . ' was published successfully')
                                ->send();
                        }),
                    Tables\Actions\Action::make('unpublish')
                        ->visible(function (Transaction $record) {
                            return $record->published;
                        })
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Transaction $record) {
                            $record->unpublish();
                        })
                        ->after(function (Transaction $record){
                            Notification::make()->danger()->title('Transaction unpublished successfully')
                                ->duration(2000)
                                ->body('Transaction ' . $record->id . ' was unpublished successfully')
                                ->send();
                        }),
                ]),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('publish')
                    ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                        $records->each->publish();
                    }),
                    Tables\Actions\BulkAction::make('unpublish')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $records->each->unpublish();
                        })
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('Change Category')
                    ->form([
                        Forms\Components\Select::make('category_id')
                        ->label('Category')
                        ->options(Category::query()->pluck('title', 'id'))
                        ->required()
                    ])
                    ->action(function (array $data, \Illuminate\Database\Eloquent\Collection $records) : void {
                        $records->each->setCategory($data['category_id']);
                    })
                    ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('Change Type')
                        ->form([
                            Forms\Components\Select::make('type')
                                ->label('Type')
                                ->options(array_map(fn($value) => strtolower($value), TransactionTypeEnum::toArrayNames()))
                                ->required()
                        ])
                        ->action(function (array $data, \Illuminate\Database\Eloquent\Collection $records) : void {
                            $records->each->setType($data['type']);
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\ImportAction::make('import')
                    ->tooltip('Importing transactions for Account.')
                    ->importer(TransactionImporter::class)
                    ->chunkSize(1000),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
            //'import' => Pages\ImportCSVTransaction::route('/import'),
        ];
    }
}
