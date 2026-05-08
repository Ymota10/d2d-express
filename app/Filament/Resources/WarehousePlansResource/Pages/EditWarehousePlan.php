<?php

namespace App\Filament\Resources\WarehousePlanResource\Pages;

use App\Filament\Resources\WarehousePlanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWarehousePlan extends EditRecord
{
    protected static string $resource = WarehousePlanResource::class;

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
