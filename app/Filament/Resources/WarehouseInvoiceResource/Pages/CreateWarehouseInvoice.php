<?php

namespace App\Filament\Resources\WarehouseInvoiceResource\Pages;

use App\Filament\Resources\WarehouseInvoiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWarehouseInvoice extends CreateRecord
{
    protected static string $resource = WarehouseInvoiceResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
