<?php

namespace App\Filament\Resources\WarehouseItemResource\Pages;

use App\Filament\Resources\WarehouseItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWarehouseItem extends ListRecords
{
    protected static string $resource = WarehouseItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
