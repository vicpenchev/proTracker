<?php

namespace App\Filament\Resources\RuleGroupResource\Pages;

use App\Filament\Resources\RuleGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRuleGroups extends ListRecords
{
    protected static string $resource = RuleGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
