<?php

namespace App\Filament\Resources\WarehousePlanResource\Pages;

use App\Filament\Resources\WarehousePlanResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWarehousePlan extends CreateRecord
{
    protected static string $resource = WarehousePlanResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
