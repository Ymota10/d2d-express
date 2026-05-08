<?php

namespace App\Filament\Resources\WarehouseInvoiceResource\Pages;

use App\Filament\Resources\WarehouseInvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWarehouseInvoice extends EditRecord
{
    protected static string $resource = WarehouseInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
