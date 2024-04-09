<?php

namespace App\Filament\Imports;

use App\Enums\RuleFieldTypeEnum;
use App\Enums\RuleTypeEnum;
use App\Enums\TransactionCreateTypeEnum;
use App\Filament\CollectionFilterBuilder\Constraints\TextConstraint\Operators\MultiContainsOperator;
use App\Filament\CollectionFilterBuilder\Constraints\TextConstraint\Operators\MultiEndsWithOperator;
use App\Filament\CollectionFilterBuilder\Constraints\TextConstraint\Operators\MultiEqualsOperator;
use App\Filament\CollectionFilterBuilder\Constraints\TextConstraint\Operators\MultiStartsWithOperator;
use App\Models\Rule;
use App\Models\RuleField;
use App\Models\RuleGroup;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use App\Filament\CollectionFilterBuilder\Constraints\TextConstraint;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TransactionImporter extends Importer
{
    use QueryBuilder\Concerns\HasConstraints, EvaluatesClosures;

    protected static ?string $model = Transaction::class;

    protected ?Collection $original_data_query;

    private array $rule_groups = [];

    private array $rules = [];

    private string $collection_filter_builder_path = 'App\Filament\CollectionFilterBuilder';

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('value')
                ->example('99.99')
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
                ->example(Carbon::now()->format('d-m-Y H:i:s'))
                ->label('Transaction date')
                ->castStateUsing(function (string $state): ?string {
                    if (blank($state)) {
                        return null;
                    }
                    try {
                        return Carbon::parse($state)->format('Y-m-d H:i:s');
                    } catch (\Exception $e) {
                        return null;
                    }
                })
                ->requiredMapping()
                ->rules(['required', 'date']),
            ImportColumn::make('from_acc')
                ->example('IE12BOFI90000112345678')
                ->label('Own Bank Account Number')
                ->ignoreBlankState()
                ->rules(['max:255']),
            ImportColumn::make('to_acc')
                ->example('IE11BOFI90000112385678')
                ->label('Recipient Bank Account Number')
                ->ignoreBlankState()
                ->rules(['max:255']),
            ImportColumn::make('notes')
                ->example('TRANSACTIO DESCRIPTION NOTES')
                ->label('Description')
                ->ignoreBlankState()
                ->rules(['max:65535']),
        ];
    }

    public function resolveRecord(): ?Transaction
    {
        $transaction = new Transaction();
        $transaction->account_id = $this->options['Account'];
        $transaction->create_type = TransactionCreateTypeEnum::IMPORTED;

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
                        ->label('Rule Group')
                        ->options(RuleGroup::query()->pluck('title', 'id'))
                ])
        ];
    }

    protected function afterCreate(): void
    {
        $record = $this->getRecord();
        $import_id = $this->import->id;
        $this->original_data_query = collect([$this->originalData]);
        $this->getRules();
        $record = $this->applyRules($record);
        $record->import_id = $import_id;
        $record->save();
    }

    private function getRules() : void
    {
        if(!count($this->rule_groups)) {
            $rules = $this->options['Rules'];
            if(count($rules)){
                foreach ($rules as $rule) {
                    /*Log::info('RULE OPTION');
                    Log::info(print_r($rule['rule'], true));*/
                    $this->rule_groups[$rule['rule']] = RuleGroup::find($rule['rule'])->related_rules()->get();
                }
                //dd(count($this->rule_groups));
                //dd($this->rule_groups);
                Log::info(print_r('$this->rule_groups', true));
                Log::info(print_r(count($this->rule_groups), true));
                /*if(count($this->rule_groups)) {
                    foreach ($this->rule_groups as $rule_group_model) {
                        Log::info('RULE GROUP RULES');
                        Log::info(print_r($rule_group_model->rules()->orderBy('order')->get(), true));
                        $this->rules[$rule_group_rule['rule']] = Rule::find($rule_group_rule['rule']);
                        //dd($rule_group_model->rules);
                        if($rule_group_model->rules) {
                            foreach ($rule_group_model->rules as $rule_group_rule) {
                                if(!isset($this->rules[$rule_group_rule['rule']])) {
                                    $this->rules[$rule_group_rule['rule']] = Rule::find($rule_group_rule['rule']);
                                }
                            }
                        }
                    }
                }*/
            }
        }
    }

    private function applyRules($record) : ?Model
    {
        if(count($this->rule_groups)){
            foreach ($this->rule_groups as $rule_group_rules) {
                if(count($rule_group_rules)) {
                    /*Log::info(print_r('$rule_group_rules', true));
                    Log::info(print_r($rule_group_rules, true));*/
                    //$rule_group_rules = $rule_group->rules;
                    foreach ($rule_group_rules as $rule_group_rule){
                        /*Log::info(print_r('$rule_group_rule', true));
                        Log::info(print_r($rule_group_rule, true));*/
                        $ruleObject = $rule_group_rule;
                        $result = null;
                        if ($ruleObject instanceof Rule) {
                            $rule_fields = $ruleObject->related_ruleFields()->get();
                            self::constraints($this->generateAvailableRuleConditions($rule_fields));
                            $rule_result_string = $this->applyRulesToQuery($this->original_data_query, $ruleObject->rules, 0, null);
                            eval('$result = ' . $rule_result_string . ';');

                            if($result) {
                                switch ($ruleObject->type) {
                                    case RuleTypeEnum::TRANSACTION_TYPE->value :
                                        $transaction_type = $ruleObject->transaction_type;
                                        if($transaction_type) {
                                            $record->type = $transaction_type;
                                        }
                                        break;
                                    case RuleTypeEnum::TRANSACTION_CATEGORY->value :
                                        $category_id = $ruleObject->category_id;
                                        if($category_id) {
                                            $record->category_id = $category_id;
                                        }
                                        break;
                                    case RuleTypeEnum::TRANSACTION_COMBINE->value :
                                        $merge_fields = $ruleObject->merge_fields;
                                        if(is_array($merge_fields) && count($merge_fields)) {
                                            $combinedArr = [];
                                            foreach ($merge_fields as $field_name) {
                                                if(!empty($this->originalData[$field_name])) {
                                                    $combinedArr[] = $this->originalData[$field_name];
                                                }
                                            }
                                            if(count($combinedArr)){
                                                $combinedValue = implode('; ', $combinedArr);
                                                $record->notes = $combinedValue;
                                            }
                                        }
                                        break;
                                    default:
                                        break;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $record;
    }

    private static function generateAvailableRuleConditions($rule_fields): array
    {
        if(!$rule_fields || !count($rule_fields)) {
            return [];
        }

        $rule_field_objects = [];

        /*
        $selected_rule_fields = array_values($rule_fields);
        $rule_fields_data = RuleField::query()->whereIn('id', $selected_rule_fields)->get(['title', 'type']);*/

        foreach ($rule_fields as $field_data) {
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
            }
        }
        return $rule_field_objects;
    }

    public function applyRulesToQuery(Collection $query, array $rules, $level = 0, $groupIndex = null): string
    {
        $conditions = [];
        foreach ($rules as $ruleIndex => $rule) {
            if ($rule['type'] === QueryBuilder\Forms\Components\RuleBuilder::OR_BLOCK_NAME) {
                $level++;
                $orConditions = [];
                foreach ($rule['data'][QueryBuilder\Forms\Components\RuleBuilder::OR_BLOCK_GROUPS_REPEATER_NAME] as $orGroupIndex => $orGroup) {
                    $orConditions[] = $this->applyRulesToQuery(
                        $query,
                        $orGroup['rules'],
                        $level,
                        $orGroupIndex
                    );
                }
                $conditions[] = '(' . implode(' OR ', $orConditions) . ')';
            } else {
                $rule_result = $this->tapOperatorFromRule($query, $rule);
                $conditions[] = ($rule_result ? 'TRUE' : 'FALSE');
            }
        }

        return '(' . implode(' AND ', $conditions) . ')';
    }

    protected function tapOperatorFromRule(Collection $query, array $rule): bool
    {
        $constraint = $this->getConstraint($rule['type']);

        if (! $constraint) {
            return false;
        }

        $operator = $rule['data'][$constraint::OPERATOR_SELECT_NAME];

        if (blank($operator)) {
            Log::info('---ERROR---');
            Log::info('No $operator found in Rule: ');
            Log::info(print_r($rule, true));
            return false;
        }

        [$operatorName, $isInverseOperator] = $constraint->parseOperatorString($operator);

        if (! $operatorName) {
            Log::info('---ERROR---');
            Log::info('No $operatorName found in Rule: ');
            Log::info(print_r($rule, true));
            return false;
        }

        $constraint
            ->settings($rule['data']['settings'])
            ->inverse($isInverseOperator);

        try {
            $constraint_reflection_class = new \ReflectionClass($constraint);
            $constraint_namespace = Str::of($constraint_reflection_class->getName())->after('QueryBuilder')->value();
            if(!str_contains($constraint_namespace, $this->collection_filter_builder_path)) {
                $constraint_namespace = $this->collection_filter_builder_path . $constraint_namespace;
            }
            $operatorObject = new ($constraint_namespace . '\\' . Str::studly($operatorName . '_apply_operator'));
            $operatorObject
                ->settings($rule['data']['settings'])
                ->inverse($isInverseOperator);

            $query = $operatorObject->apply($query, $constraint->getLabel());
        } catch (\Exception $exception) {
            Log::info('---ERROR---');
            Log::info($exception->getMessage());
            return false;
        }

        $constraint
            ->settings(null)
            ->inverse(null);

        if(!($query instanceof Collection) || $query->isEmpty()) {
            return false;
        }
        return true;
    }
}
