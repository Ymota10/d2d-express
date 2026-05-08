<?php

namespace App\Filament\Resources\WarehouseInvoiceResource\Pages;

use App\Filament\Resources\WarehouseInvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWarehouseInvoice extends ListRecords
{
    protected static string $resource = WarehouseInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
