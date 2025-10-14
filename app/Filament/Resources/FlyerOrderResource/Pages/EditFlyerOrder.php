<?php

namespace App\Filament\Resources\FlyerOrderResource\Pages;

use App\Filament\Resources\FlyerOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFlyerOrder extends EditRecord
{
    protected static string $resource = FlyerOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
