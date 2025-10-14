<?php

namespace App\Filament\Resources\ShipperResource\Pages;

use App\Filament\Resources\ShipperResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateShipper extends CreateRecord
{
    protected static string $resource = ShipperResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
