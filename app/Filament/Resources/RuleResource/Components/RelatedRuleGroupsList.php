<?php

namespace App\Filament\Resources\RuleResource\Components;

use App\Filament\Resources\RuleGroupResource;
use App\Models\RuleGroup;
use Faker\Provider\Text;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Livewire\Component;

class RelatedRuleGroupsList extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    public $record;

    protected function getTableQuery(): Builder|Relation|null
    {
        return RuleGroup::query()->whereIn('id', $this->record->rule_groups()->pluck('rule_groups.id'));
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
            TextColumn::make('description')
                ->wrap()
                ->limit(30)
                ->searchable()
                ->tooltip(fn (?string $state): ?string => $state),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            EditAction::make()
                ->label('Edit Rule Group')
                ->url(function (RuleGroup $record, RuleGroupResource $resource) {
                    return $resource::getUrl('edit', ['record' => $record]);
                }),
            DeleteAction::make()
        ];
    }

    public function render(): View
    {
        return view('filament.livewire.related_rule_groups-list');
    }
}
