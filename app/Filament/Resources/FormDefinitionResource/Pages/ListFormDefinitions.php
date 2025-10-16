<?php

namespace App\Filament\Resources\FormDefinitionResource\Pages;

use App\Filament\Resources\FormDefinitionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFormDefinitions extends ListRecords
{
    protected static string $resource = FormDefinitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
