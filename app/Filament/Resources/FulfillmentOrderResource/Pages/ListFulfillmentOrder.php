<?php

namespace App\Filament\Resources\FulfillmentOrderResource\Pages;

use App\Filament\Resources\FulfillmentOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFulfillmentOrder extends ListRecords
{
    protected static string $resource = FulfillmentOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
