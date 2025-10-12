<?php

namespace App\Filament\Resources\ShipperResource\Pages;

use App\Filament\Resources\ShipperResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditShipper extends EditRecord
{
    protected static string $resource = ShipperResource::class;

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
