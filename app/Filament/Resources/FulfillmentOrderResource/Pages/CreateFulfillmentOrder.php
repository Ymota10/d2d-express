<?php

namespace App\Filament\Resources\FulfillmentOrderResource\Pages;

use App\Filament\Resources\FulfillmentOrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFulfillmentOrder extends CreateRecord
{
    protected static string $resource = FulfillmentOrderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
