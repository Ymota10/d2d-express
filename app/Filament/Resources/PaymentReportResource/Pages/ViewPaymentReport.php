<?php

namespace App\Filament\Resources\PaymentReportResource\Pages;

use App\Filament\Resources\PaymentReportResource;
use App\Filament\Resources\PaymentReportResource\Widgets\OrdersInReportTable;
use Filament\Resources\Pages\ViewRecord;

class ViewPaymentReport extends ViewRecord
{
    protected static string $resource = PaymentReportResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            OrdersInReportTable::class,
        ];
    }
}
