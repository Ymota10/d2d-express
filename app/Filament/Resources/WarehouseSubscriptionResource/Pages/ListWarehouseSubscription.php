<?php

namespace App\Filament\Resources\WarehouseSubscriptionResource\Pages;

use App\Filament\Resources\WarehouseSubscriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWarehouseSubscription extends ListRecords
{
    protected static string $resource = WarehouseSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
