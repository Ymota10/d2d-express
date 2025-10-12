<?php

namespace App\Filament\Resources\FinancialAnalysisResource\Pages;

use App\Filament\Resources\FinancialAnalysisResource;
use App\Models\Order;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListFinancialAnalyses extends ListRecords
{
    protected static string $resource = FinancialAnalysisResource::class;

    // protected function getTableQuery(): Builder
    // {
    //     return Order::with(['user', 'area'])->whereIn('status', ['success_delivery', 'partial_return', 'undelivered']);
    // }

    protected function getHeaderWidgets(): array
    {
        return [\App\Filament\Widgets\FinancialSummary::class];
    }
}
