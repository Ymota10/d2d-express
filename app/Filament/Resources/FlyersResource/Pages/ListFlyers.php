<?php

namespace App\Filament\Resources\FlyersResource\Pages;

use App\Filament\Resources\FlyersResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFlyers extends ListRecords
{
    protected static string $resource = FlyersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
