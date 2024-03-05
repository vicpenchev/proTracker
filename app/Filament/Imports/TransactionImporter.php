<?php

namespace App\Filament\Imports;

use App\Enums\RuleFieldTypeEnum;
use App\Enums\RuleTypeEnum;
use App\Enums\TransactionCreateTypeEnum;
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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TransactionImporter extends Importer
{
    use QueryBuilder\Concerns\HasConstraints, EvaluatesClosures;

    protected static ?string $model = Transaction::class;

    protected ?Collection $original_data_query;

    private ?Model $current_record;

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
                        ->label('Rule')
                        ->options(Rule::query()->pluck('title', 'id'))
                ])
        ];
    }

    protected function afterCreate(): void
    {
        $record = $this->getRecord();
        $import_id = $this->import->id;
        $record = $this->applyRules($record);
        $record->date = Carbon::now();
        $record->import_id = $import_id;
        $record->save();
    }

    private function applyRules($record) : ?Model
    {
        $this->original_data_query = collect([$this->originalData]);
        $rules = $this->options['Rules'];
        if(count($rules)){
            foreach ($rules as $rule) {
                /*Log::info('----------------------------------');
                Log::info('----------------------------------');*/
                $ruleObject = Rule::find($rule['rule']);
                $result = null;
                if ($ruleObject) {
                   /* Log::info('!!!RULES!!!:');
                    Log::info(print_r($ruleObject->rules, true));*/
                    $rule_fields = $ruleObject->rule_fields;
                    self::constraints($this->generateAvailableRuleConditions($rule_fields));
                    $rule_result_string = $this->applyRulesToQuery($this->original_data_query, $ruleObject->rules, 0, null);
                    eval('$result = ' . $rule_result_string . ';');
                    /*Log::info('RULES RESULT STRING: ');
                    Log::info(print_r($rule_result_string, true));*/
                    /*Log::info('RULES RESULT: ');
                    Log::info(print_r(($result ? 'TRUE' : 'FALSE'), true));
                    Log::info('RULE TYPE: ');
                    Log::info(print_r(($ruleObject->type), true));
                    Log::info('----------------------------------');*/

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
        return $record;
    }

    private static function generateAvailableRuleConditions($rule_fields): array
    {
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
            $operatorObject = new ('App\Filament\CollectionFilterBuilder' . $constraint_namespace . '\\' . Str::studly($operatorName . '_operator'));
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
