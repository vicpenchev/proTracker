<?php

namespace App\Filament\Resources\RuleGroupResource\Pages;

use App\Filament\Resources\RuleGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRuleGroup extends EditRecord
{
    protected static string $resource = RuleGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
