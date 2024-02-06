<?php

namespace App\Filament\Resources\RuleFieldResource\Pages;

use App\Filament\Resources\RuleFieldResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRuleField extends EditRecord
{
    protected static string $resource = RuleFieldResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
