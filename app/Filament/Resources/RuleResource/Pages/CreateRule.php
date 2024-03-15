<?php

namespace App\Filament\Resources\RuleResource\Pages;

use App\Filament\Resources\RuleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRule extends CreateRecord
{
    protected static string $resource = RuleResource::class;

    protected static bool $canCreateAnother = false;

    protected function getFormActions(): array
    {
        return [];
    }

}
