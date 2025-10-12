<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderStats extends BaseWidget
{
    protected function getStats(): array
    {
        $totalOrders = Order::count();
        $successfulOrders = Order::where('status', 'success_delivery')->count();
        $unsuccessfulOrders = Order::where('status', 'undelivered')->count();
        $cashCollected = Order::where('status', 'success_delivery')->sum('cod_amount');

        return [
            Stat::make('Total Orders', $totalOrders),
            Stat::make('Successful Orders', $successfulOrders)
                ->color('success'),
            Stat::make('Unsuccessful Orders', $unsuccessfulOrders)
                ->color('danger'),
            Stat::make('Cash Collected', number_format($cashCollected, 2).' EGP')
                ->color('primary'),
        ];
    }
}
