<?php

namespace App\Filament\Imports;

use App\Enums\RuleFieldTypeEnum;
use App\Enums\TransactionCreateTypeEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Category;
use App\Models\Rule;
use App\Models\RuleField;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TransactionImporter extends Importer
{
    use QueryBuilder\Concerns\HasConstraints, EvaluatesClosures;

    protected static ?string $model = Transaction::class;

    protected ?Collection $original_data_query;
    private bool $rule_met = false;

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
            Repeater::make('Rules')
                ->label('Rules')
                ->helperText('Rules that will be applied to all records. The rules will be applied based on the order.')
                ->schema([
                    Select::make('rule')
                        ->label('Rule')
                        ->options(Rule::query()->pluck('title', 'id'))
                ])
            /*TextInput::make('combine_descriptions')
                ->label('Combine Description Fields from CSV')
                ->helperText('Use to combine descriptions from multiple fields. Values are separated by ";". Example: {csv_column_name1}||{csv_column_name2}'),
            Textarea::make('type_mapping')
                ->label('Type Mapping')
                ->helperText('Type Mapping. Example: [{"column_name":"Column Name","column_value":"Receipt for Booking","type":"EXPENSE"},{"column_name":"Column Name 2","column_value":"Salary Payment","type":"INCOME"}]'),
            */
            /*Textarea::make('category_mapping')
                ->label('Category Mapping')
                ->helperText('Category Mapping. Searches if string is contained in a specified column content. Example: [{"column_name":"Column Name","column_value":"Billa","category":"Food"},{"column_name":"Column Name 2","column_value":"Gasoline","type":"Bills"}]'),
            */
        ];
    }

    protected function afterCreate(): void
    {
        $record = $this->getRecord();

        //$this->combineDescriptions($record);
        //$this->setTypes($record);
        //$this->setCategories($record);
        $this->applyRules($record);
        $import_id = $this->import->id;
        $record->import_id = $import_id;
        $record->save();
    }

    private function applyRules($record)
    {
        $this->original_data_query = collect([$this->originalData]);

        //$text = 'ДТ';
        //$data = $this->original_data_query->where('Тип', 'like', "%$text%")->all();
        //$data = $this->original_data_query->filter(function ($item) use ($text) { return strpos($item['Тип'], $text) !== false;})->all();
        //Log::info('TEST QUERY WHERE');
        //Log::info(print_r($data,true));

        $rules = $this->options['Rules'];
        if(count($rules)){
            foreach ($rules as $rule) {
                //Log::info(print_r($rule['rule'], true));
                $ruleObject = Rule::find($rule['rule']);
                if ($ruleObject) {
                    $rule_fields = $ruleObject->rule_fields;
                    self::constraints($this->generateAvailableRuleConditions($rule_fields));
                    $this->rule_met = false;
                    $query = $this->applyRulesToQuery($this->original_data_query, $ruleObject->rules);
                    /*Log::info(print_r('RULES: ', true));
                    Log::info(print_r($ruleObject->rules, true));
                    Log::info(print_r('QUERY: ', true));
                    Log::info(print_r($query, true));*/
                    /*$ruleBuilder = QueryBuilder\Forms\Components\RuleBuilder::make('rules')
                        ->constraints($this->generateAvailableRuleConditions($rule_fields));*/
                    //Log::info(print_r($ruleObject, true));

                }
            }
        }
        //$ruleObject =

        //Log::info(print_r($this->originalData, true));
        //Log::info(print_r($rules, true));
    }

    private static function generateAvailableRuleConditions($rule_fields): array
    {
        //Session::remove('rule_fields');
        if(!$rule_fields || !count($rule_fields)) {
            return [];
        }

        $rule_field_objects = [];
        $selected_rule_fields = array_values($rule_fields);
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
                    $rule_field_objects[] = TextConstraint::make(Str::slug($field_data->title))->label($field_data->title);
                    break;
            }
        }
        return $rule_field_objects;
    }

    public function applyRulesToQuery(Collection $query, array $rules): bool | Collection
    {
        foreach ($rules as $ruleIndex => $rule) {

            if ($rule['type'] === QueryBuilder\Forms\Components\RuleBuilder::OR_BLOCK_NAME) {
                foreach ($rule['data'][QueryBuilder\Forms\Components\RuleBuilder::OR_BLOCK_GROUPS_REPEATER_NAME] as $orGroupIndex => $orGroup) {
                    $rule_result = $this->applyRulesToQuery(
                        $query,
                        $orGroup['rules']
                    );
                    if($rule_result) {
                        Log::info('OR GROUP RULE IS TRUE');
                        Log::info(print_r($orGroup['rules'], true));
                        break;
                    }
                }

                Log::info(print_r('applyRulesToQuery OR_BLOCK_NAME: ' . $rule['type'], true));
                /*$this->original_data_query->where(function (Collection $query) use ($rule) {
                    $isFirst = true;

                    foreach ($rule['data'][RuleBuilder::OR_BLOCK_GROUPS_REPEATER_NAME] as $orGroupIndex => $orGroup) {
                        $query->{$isFirst ? 'where' : 'orWhere'}(function (Collection $query) use ($orGroup, $orGroupIndex) {
                            $this->applyRulesToQuery(
                                $query,
                                $orGroup['rules']
                            );
                        });

                        $isFirst = false;
                    }
                });*/

                continue;
            }

            $this->rule_met = $this->tapOperatorFromRule($query, $rule);
        }

        return $this->rule_met;
    }

    protected function tapOperatorFromRule(Collection $query, array $rule): bool
    {
        //Log::info(print_r($this->constraints, true));
        $constraint = $this->getConstraint($rule['type']);


        /*if($constraint instanceof TextConstraint) {
            Log::info('CONSTRAINT: ');
            Log::info(print_r($constraint, true));
        }*/

        if (! $constraint) {
            return false;
        }

        $operator = $rule['data'][$constraint::OPERATOR_SELECT_NAME];
        //Log::info('OPERATOR 1: ');
        //Log::info(print_r($operator, true));
        if (blank($operator)) {
            return false;
        }

        [$operatorName, $isInverseOperator] = $constraint->parseOperatorString($operator);
        Log::info('RULE TYPE: ');
        Log::info(print_r($rule['type'], true));
        Log::info('OPERATOR NAME: ');
        Log::info(print_r($operatorName, true));
        Log::info('OPERATOR isInverse: ');
        Log::info(print_r($isInverseOperator, true));
        Log::info('OPERATOR settings: ');
        Log::info(print_r($rule['data']['settings'], true));

        //$operator = $constraint->getOperator($operatorName);

        if (! $operatorName) {
            return false;
        }

        /*if($operatorName == 'equals') {
            $text = trim($rule['data']['settings']['text']);
            $text = Str::lower($text);
            //$text = 'test';

            $result = $query->{$isInverseOperator ? 'whereNot' : 'where'}($constraint->getLabel(), '=', $text);

            Log::info('QUERY ');
            Log::info(print_r($result, true));
            Log::info('END QUERY ');
        }*/





        $constraint
            ->settings($rule['data']['settings'])
            ->inverse($isInverseOperator);

        //if($constraint instanceof TextConstraint) {
            //if($operatorName == 'equals') {
        try {
            $constraint_reflection_class = new \ReflectionClass($constraint);
            $constraint_namespace = Str::of($constraint_reflection_class->getName())->after('QueryBuilder')->value();
            Log::info('constraint_namespace ');
            Log::info(print_r($constraint_namespace, true));
            Log::info('END constraint_namespace ');
            $operatorObject = new ('App\Filament\CollectionFilterBuilder' . $constraint_namespace . '\\' . Str::studly($operatorName . '_operator'));
            $operatorObject
                ->settings($rule['data']['settings'])
                ->inverse($isInverseOperator);

            $query = $operatorObject->apply($query, $constraint->getLabel());
        } catch (\Exception $exception) {
            Log::info($exception->getMessage());
            return false;
        }

        Log::info('QUERY AFTER APPLY ');
        Log::info(print_r($query->all(), true));
        Log::info('END QUERY AFTER APPLY ');
            //}
        //}

            /*$operator
                ->constraint($constraint)
                ->settings($rule['data']['settings'])
                ->inverse($isInverseOperator);*/

        Log::info('CONSTRAINT LABEL: ');
        Log::info(print_r($constraint->getLabel(), true));

        /*Log::info('CONSTRAINT Attribute: ');
        Log::info(print_r($operator->getConstraint()->getAttributeForQuery(), true));
*/
/*
        Log::info('OPERATOR: ');
        Log::info(print_r($operator, true));*/
        //$callback($operator);

        $constraint
            ->settings(null)
            ->inverse(null);

        if(!($query instanceof Collection) || $query->isEmpty()) {
            return false;
        }
        return true;

        /*$operator
            ->constraint(null)
            ->settings(null)
            ->inverse(null);*/
    }

    private function createOperator()
    {

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
