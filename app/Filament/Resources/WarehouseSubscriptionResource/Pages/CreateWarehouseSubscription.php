<?php

namespace App\Filament\Resources\WarehouseSubscriptionResource\Pages;

use App\Filament\Resources\WarehouseSubscriptionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWarehouseSubscription extends CreateRecord
{
    protected static string $resource = WarehouseSubscriptionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
