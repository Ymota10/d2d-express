<?php

namespace App\Filament\Resources\WarehousePlanResource\Pages;

use App\Filament\Resources\WarehousePlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWarehousePlan extends ListRecords
{
    protected static string $resource = WarehousePlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
