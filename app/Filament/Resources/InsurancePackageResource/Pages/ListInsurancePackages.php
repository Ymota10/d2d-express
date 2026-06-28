<?php

namespace App\Filament\Resources\InsurancePackageResource\Pages;

use App\Filament\Resources\InsurancePackageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInsurancePackages extends ListRecords
{
    protected static string $resource = InsurancePackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
