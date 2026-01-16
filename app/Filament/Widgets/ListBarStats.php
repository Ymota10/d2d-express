<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ListBarStats extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();

        // ✅ If user is not admin, filter only their orders
        $query = Order::query();
        if (! $user->isAdmin()) {
            $query->where('users_id', $user->id);
        }

        $totalOrders = $query->count();

        $totalMoney = $query->sum('cod_amount');

        $todayOrders = (clone $query)
            ->whereIn('status', ['success_delivery', 'partial_return'])
            ->whereDate('updated_at', today())
            ->where('is_collected', false)
            ->count();

        return [
            Stat::make('Total Orders', number_format($totalOrders))
                ->description('All-time total orders')
                ->descriptionIcon('heroicon-m-chart-bar', 'before')
                ->color('info')
                ->icon('heroicon-o-shopping-bag')
                ->extraAttributes([
                    'class' => 'bg-red-500 text-white rounded-xl shadow-lg p-4',
                ]),

            Stat::make('Total COD Amount', number_format($totalMoney, 2))
                ->icon('heroicon-o-currency-dollar')
                ->color('success')
                ->extraAttributes([
                    'class' => 'bg-red-500 text-white rounded-xl shadow-lg p-4',
                ]),

            Stat::make('Delivered Today', $todayOrders)
                ->description('Orders completed successfully')
                ->descriptionIcon('heroicon-m-check-badge', 'before')
                ->color('info')
                ->icon('heroicon-o-truck')
                ->extraAttributes([
                    'class' => 'bg-yellow-100 shadow-lg rounded-lg transform hover:scale-105 transition duration-300 p-4',
                ]),
        ];
    }
}
