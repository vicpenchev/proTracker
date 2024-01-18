<?php

namespace App\Filament\Imports;

use App\Enums\TransactionCreateTypeEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class TransactionImporter extends Importer
{
    protected static ?string $model = Transaction::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('value')
                ->label('Transaction Sum')
                ->castStateUsing(function (string $state): ?float {
                    if (blank($state)) {
                        return null;
                    }

                    $state = preg_replace('/[^0-9.]/', '', $state);
                    $state = floatval($state);

                    return round($state, precision: 2);
                })
                ->requiredMapping()
                ->numeric(decimalPlaces: 2)
                ->rules(['required', 'numeric']),
            ImportColumn::make('date')
                ->label('Transaction date')
                ->castStateUsing(function (string $state): ?string {
                    if (blank($state)) {
                        return null;
                    }

                    return Carbon::parse($state)->format('Y-m-d H:i:s');
                })
                ->requiredMapping()
                ->rules(['required', 'date']),
            ImportColumn::make('from_acc')
                ->label('Own Bank Account Number')
                ->ignoreBlankState()
                ->rules(['max:255']),
            ImportColumn::make('to_acc')
                ->label('Recipient Bank Account Number')
                ->ignoreBlankState()
                ->rules(['max:255']),
            ImportColumn::make('notes')
                ->label('Description')
                ->ignoreBlankState()
                ->rules(['max:65535']),
        ];
    }

    public function resolveRecord(): ?Transaction
    {
        // return Transaction::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);
        $transaction = new Transaction();
        $transaction->account_id = $this->options['Account'];
        $transaction->create_type = TransactionCreateTypeEnum::IMPORTED;

        //dd($this->options['Account']);

        return $transaction;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your transaction import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    public static function getOptionsFormComponents(): array
    {
        return [
            Select::make('Account')
                ->searchable()
                ->preload()
                ->relationship('account', 'title')
                ->required(),
            TextInput::make('combine_descriptions')
                ->label('Combine Description Fields from CSV')
                ->helperText('Use to combine descriptions from multiple fields. Values are separated by ";". Example: {csv_column_name1}||{csv_column_name2}'),
            Textarea::make('type_mapping')
                ->label('Type Mapping')
                ->helperText('Type Mapping. Example: [{"column_name":"Column Name","column_value":"Receipt for Booking","type":"EXPENSE"},{"column_name":"Column Name 2","column_value":"Salary Payment","type":"INCOME"}]'),
            /*Textarea::make('category_mapping')
                ->label('Category Mapping')
                ->helperText('Category Mapping. Searches if string is contained in a specified column content. Example: [{"column_name":"Column Name","column_value":"Billa","category":"Food"},{"column_name":"Column Name 2","column_value":"Gasoline","type":"Bills"}]'),
            */
        ];
    }

    protected function afterCreate(): void
    {
        $record = $this->getRecord();
        $this->combineDescriptions($record);
        $this->setTypes($record);
        //$this->setCategories($record);
        $import_id = $this->import->id;
        $record->import_id = $import_id;
        $record->save();
    }

    private function setTypes($record): void
    {
        $types = TransactionTypeEnum::toArray();

        if($this->options['type_mapping']) {
            $type_rules = json_decode($this->options['type_mapping']);
            if(is_array($type_rules)) {
                foreach ($type_rules as $type_rule) {
                    if(isset($type_rule->column_name) && isset($type_rule->column_value) && isset($type_rule->type)) {
                        $type_columnName = $type_rule->column_name;
                        $type_columnValue = $type_rule->column_value;
                        $type_title = $type_rule->type;
                        //Log::info('$type_columnName: ' . $type_columnName);
                        //Log::info('$type_columnValue: ' . $type_columnValue);
                        //Log::info('$type_title: ' . $type_title);
                        if(isset($this->originalData[$type_columnName])) {
                            if($type_columnValue == $this->originalData[$type_columnName]) {
                                $type = $types[$type_title] ?? null;
                                if ($type) {
                                    $record->type = $type;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    private function setCategories($record): void
    {
        $categories_data = Category::all(['id', 'title'])->toArray();
        $categories = [];
        if(count($categories_data)) {
            foreach ($categories_data as $category) {
                $categories[strtolower($category['title'])] = $category['id'];
            }
        }
        //Log::info(print_r($categories, true));
        //exit;

        if($this->options['type_mapping'] && count($categories)) {
            $category_rules = json_decode($this->options['category_mapping']);
            if(is_array($category_rules)) {
                foreach ($category_rules as $category_rule) {
                    if(isset($category_rule->column_name) && isset($category_rule->column_value) && isset($category_rule->type)) {
                        $category_columnName = $category_rule->column_name;
                        $category_columnValue = strtolower($category_rule->column_value);
                        $category_title = strtolower($category_rule->type);
                        //Log::info('$category_columnName: ' . $category_columnName);
                        //Log::info('$category_columnValue: ' . $category_columnValue);
                        //Log::info('$category_title: ' . $category_title);
                        if(isset($this->originalData[$category_columnName])) {
                            if(str_contains(strtolower($this->originalData[$category_columnName]), $category_columnValue)) {
                                $category_id = $categories[$category_title] ?? null;
                                if ($category_id) {
                                    $record->category_id = $category_id;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    private function combineDescriptions($record): void
    {
        if($this->options['combine_descriptions']) {
            $columns = explode('||', $this->options['combine_descriptions']);
            $description = '';
            foreach ($columns as $column) {
                $column = trim($column);
                if(isset($this->originalData[$column])) {
                    $description .= $this->originalData[$column] . '; ';
                }
            }
            $record->notes = $description;
        }
    }
}
