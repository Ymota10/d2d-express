<?php

namespace App\Filament\Resources\InsurancePackageResource\Pages;

use App\Filament\Resources\InsurancePackageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInsurancePackage extends EditRecord
{
    protected static string $resource = InsurancePackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
