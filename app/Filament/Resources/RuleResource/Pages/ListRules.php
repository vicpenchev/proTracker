<?php

namespace App\Filament\Resources\RuleResource\Pages;

use App\Filament\Resources\RuleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRules extends ListRecords
{
    protected static string $resource = RuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
