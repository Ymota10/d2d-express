<?php

namespace App\Filament\Resources\FulfillmentOrderResource\Pages;

use App\Filament\Resources\FulfillmentOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFulfillmentOrder extends EditRecord
{
    protected static string $resource = FulfillmentOrderResource::class;

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
