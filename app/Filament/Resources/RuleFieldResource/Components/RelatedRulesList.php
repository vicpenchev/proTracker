<?php

namespace App\Filament\Resources\RuleFieldResource\Components;

use App\Enums\RuleTypeEnum;
use App\Filament\Resources\RuleResource;
use App\Models\Rule;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Livewire\Component;

class RelatedRulesList extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    public $record;

    protected function getTableQuery(): Builder|Relation|null
    {
        return Rule::query()->whereIn('id', $this->record->rules()->pluck('rules.id'));
    }

    public function mount($record)
    {
        $this->record = $record;
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('title')
                ->wrap()
                ->limit(30)
                ->tooltip(function (TextColumn $column): ?string {
                    $state = $column->getState();

                    if (strlen($state) <= $column->getCharacterLimit()) {
                        return null;
                    }

                    return $state;
                })
                ->searchable(),
            TextColumn::make('type')
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
        ];
    }

    protected function getTableActions(): array
    {
        return [
            EditAction::make()
                ->label('Edit Rule')
                ->url(function (Rule $record, RuleResource $resource) {
                    return $resource::getUrl('edit', ['record' => $record]);
                }),
            DeleteAction::make()
                ->requiresConfirmation()
                ->action(fn (Rule $rule) => $rule->delete())
                ->hidden(function (Rule $record) {
                    if($record->rule_groups()->count() > 0){
                        return true;
                    }
                    return false;
                })
        ];
    }

    public function render(): View
    {
        return view('filament.livewire.related_rules-list');
    }
}
