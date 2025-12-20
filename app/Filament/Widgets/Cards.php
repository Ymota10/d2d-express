<?php

namespace App\Filament\Widgets;

use App\Models\Area;
use App\Models\City;
use App\Models\Order;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class Cards extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();

        // Base order query
        $orderQuery = Order::query();

        // If not admin → show only orders that belong to this user
        if (! $user?->isAdmin()) {
            $orderQuery->where('users_id', $user->id);
        }

        // Order status counts (based on filtered query)
        $pickupRequest = (clone $orderQuery)->where('status', 'pickup_request')->count();
        $warehouseReceived = (clone $orderQuery)->where('status', 'warehouse_received')->count();
        $outForDelivery = (clone $orderQuery)->where('status', 'out_for_delivery')->count();

        // ✅ Combine time_scheduled + returned_to_warehouse
        $inProgress = (clone $orderQuery)
            ->whereIn('status', ['time_scheduled', 'returned_to_warehouse'])
            ->count();

        $successDelivery = (clone $orderQuery)
            ->whereIn('status', ['success_delivery', 'partial_return'])
            ->count();
        $undelivered = (clone $orderQuery)->where('status', 'undelivered')->count();

        return array_filter([
            // ✅ Admin-only stats
            $user?->isAdmin()
                ? Stat::make('Total Couriers', User::where('management', 'courier')->count())
                    ->description('5% increase')
                    ->descriptionIcon('heroicon-m-arrow-trending-up')
                    ->color('success')
                    ->chart([37, 30, 32, 35, 34, 40, 42])
                    ->icon('healthicons-o-truck-driver')
                : null,

            $user?->isAdmin()
                ? Stat::make('Total Cities', City::count())
                    ->description('3% decrease')
                    ->descriptionIcon('heroicon-m-arrow-trending-down')
                    ->color('danger')
                    ->chart([37, 30, 32, 35, 34, 40, 42])
                    ->icon('fluentui-globe-location-24-o')
                : null,

            $user?->isAdmin()
                ? Stat::make('Total Areas', Area::count())
                    ->description('7% increase')
                    ->descriptionIcon('heroicon-m-arrow-trending-up')
                    ->color('success')
                    ->chart([37, 30, 32, 35, 34, 40, 42])
                    ->icon('fluentui-globe-surface-20-o')
                : null,

            // ✅ Order status cards (filtered per user)
            Stat::make('Pickup Requests', $pickupRequest)
                ->icon('heroicon-o-hand-raised')
                ->color('warning')
                ->extraAttributes(['class' => 'bg-blue-100 shadow-md rounded-lg p-4']),

            Stat::make('Warehouse Received', $warehouseReceived)
                ->icon('heroicon-o-home-modern')
                ->color('info')
                ->extraAttributes(['class' => 'bg-indigo-100 shadow-md rounded-lg p-4']),

            Stat::make('Out For Delivery', $outForDelivery)
                ->icon('heroicon-o-truck')
                ->color('success')
                ->extraAttributes(['class' => 'bg-green-100 shadow-md rounded-lg p-4']),

            // ✅ Combined “In Progress” card
            Stat::make('In Progress', $inProgress)
                ->icon('heroicon-m-chevron-double-up')
                ->color('gray')
                ->extraAttributes(['class' => 'bg-purple-100 shadow-md rounded-lg p-4']),

            Stat::make('Success Delivery', $successDelivery)
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->extraAttributes(['class' => 'bg-green-200 shadow-md rounded-lg p-4']),

            Stat::make('Undelivered', $undelivered)
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->extraAttributes(['class' => 'bg-red-100 shadow-md rounded-lg p-4']),
        ]);
    }
}
