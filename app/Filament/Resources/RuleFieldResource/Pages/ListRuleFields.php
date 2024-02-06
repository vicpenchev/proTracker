<?php

namespace App\Filament\Resources\RuleFieldResource\Pages;

use App\Filament\Resources\RuleFieldResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRuleFields extends ListRecords
{
    protected static string $resource = RuleFieldResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
