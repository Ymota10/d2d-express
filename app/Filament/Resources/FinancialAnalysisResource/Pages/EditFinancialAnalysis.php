<?php

namespace App\Filament\Resources\FinancialAnalysisResource\Pages;

use App\Filament\Resources\FinancialAnalysisResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFinancialAnalysis extends EditRecord
{
    protected static string $resource = FinancialAnalysisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
