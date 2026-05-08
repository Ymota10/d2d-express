<?php

namespace App\Filament\Resources\WarehouseItemResource\Pages;

use App\Filament\Resources\WarehouseItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWarehouseItem extends CreateRecord
{
    protected static string $resource = WarehouseItemResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
