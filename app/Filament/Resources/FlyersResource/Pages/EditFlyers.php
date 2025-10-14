<?php

namespace App\Filament\Resources\FlyersResource\Pages;

use App\Filament\Resources\FlyersResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFlyers extends EditRecord
{
    protected static string $resource = FlyersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
