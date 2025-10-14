<?php

namespace App\Filament\Resources\PaymentReportResource\Pages;

use App\Filament\Resources\PaymentReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaymentReports extends ListRecords
{
    protected static string $resource = PaymentReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
