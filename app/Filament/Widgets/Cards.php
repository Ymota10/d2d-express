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
    protected function getColumns(): int
    {
        return 4;
    }

    protected function getHeading(): ?string
    {
        $user = auth()->user();

        return 'Ahlan, '.($user?->name ?? 'User').' 👋 
        — Today\'s Overview';
    }

    protected function getStats(): array
    {

        $user = auth()->user();

        /*
         * ============================================================
         * BASE ORDER QUERY
         * ============================================================
         */

        $orderQuery = Order::query();

        // If not admin → show only this user's orders
        if (! $user?->isAdmin()) {
            $orderQuery->where('users_id', $user->id);
        }

        /*
         * ============================================================
         * TODAY'S ORDERS
         * ============================================================
         *
         * We use updated_at because the order's status changes during
         * its lifecycle.
         *
         * This means the cards show orders whose current status was
         * updated today, instead of the lifetime total.
         */

        $orderQuery->whereDate('updated_at', today());

        /*
         * ============================================================
         * ORDER STATUS COUNTS
         * ============================================================
         */

        $pickupRequest = (clone $orderQuery)
            ->where('status', 'pickup_request')
            ->count();

        $warehouseReceived = (clone $orderQuery)
            ->where('status', 'warehouse_received')
            ->count();

        $outForDelivery = (clone $orderQuery)
            ->where('status', 'out_for_delivery')
            ->count();

        $timeScheduled = (clone $orderQuery)
            ->where('status', 'time_scheduled')
            ->count();

        $returnedToWarehouse = (clone $orderQuery)
            ->where('status', 'returned_to_warehouse')
            ->count();

        $undelivered = (clone $orderQuery)
            ->where('status', 'undelivered')
            ->count();

        $successDelivery = (clone $orderQuery)
            ->where('status', 'success_delivery')
            ->count();

        $collectedCashSales = Order::query()
            ->whereDate('updated_at', today())
            ->whereIn('status', [
                'success_delivery',
                'partial_return',
            ]);

        if (! $user?->isAdmin()) {
            $collectedCashSales->where('users_id', $user->id);
        }

        $collectedCashSales = $collectedCashSales->sum('cod_amount');

        /*
         * ============================================================
         * RETURN CARDS
         * ============================================================
         */

        return array_filter([

            // ========================================================
            // ADMIN ONLY
            // ========================================================

            // $user?->isAdmin()
            //     ? Stat::make(
            //         'Total Couriers',
            //         User::where('management', 'courier')->count()
            //     )
            //         ->description('5% increase')
            //         ->descriptionIcon('heroicon-m-arrow-trending-up')
            //         ->color('success')
            //         ->chart([37, 30, 32, 35, 34, 40, 42])
            //         ->icon('healthicons-o-truck-driver')
            //     : null,

            // $user?->isAdmin()
            //     ? Stat::make(
            //         'Total Cities',
            //         City::count()
            //     )
            //         ->description('3% decrease')
            //         ->descriptionIcon('heroicon-m-arrow-trending-down')
            //         ->color('danger')
            //         ->chart([37, 30, 32, 35, 34, 40, 42])
            //         ->icon('fluentui-globe-location-24-o')
            //     : null,

            // $user?->isAdmin()
            //     ? Stat::make(
            //         'Total Areas',
            //         Area::count()
            //     )
            //         ->description('7% increase')
            //         ->descriptionIcon('heroicon-m-arrow-trending-up')
            //         ->color('success')
            //         ->chart([37, 30, 32, 35, 34, 40, 42])
            //         ->icon('fluentui-globe-surface-20-o')
            //     : null,

            // ========================================================
            // TODAY'S ORDER STATUS CARDS
            // ========================================================

            Stat::make(
                'Pickup Requests',
                $pickupRequest
            )
                ->icon('heroicon-o-hand-raised')
                ->color('warning')
                ->extraAttributes([
                    'class' => 'bg-blue-100 shadow-md rounded-lg p-4',
                ]),

            Stat::make(
                'Warehouse Received',
                $warehouseReceived
            )
                ->icon('heroicon-o-home-modern')
                ->color('info')
                ->extraAttributes([
                    'class' => 'bg-indigo-100 shadow-md rounded-lg p-4',
                ]),

            Stat::make(
                'Out For Delivery',
                $outForDelivery
            )
                ->icon('heroicon-o-truck')
                ->color('success')
                ->extraAttributes([
                    'class' => 'bg-green-100 shadow-md rounded-lg p-4',
                ]),

            Stat::make(
                'Time Scheduled',
                $timeScheduled
            )
                ->icon('heroicon-o-clock')
                ->color('warning')
                ->extraAttributes([
                    'class' => 'bg-yellow-100 shadow-md rounded-lg p-4',
                ]),

            Stat::make(
                'Returned to Warehouse',
                $returnedToWarehouse
            )
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('gray')
                ->extraAttributes([
                    'class' => 'bg-purple-100 shadow-md rounded-lg p-4',
                ]),

            Stat::make(
                'Undelivered',
                $undelivered
            )
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->extraAttributes([
                    'class' => 'bg-red-100 shadow-md rounded-lg p-4',
                ]),

            Stat::make(
                'Success Delivery',
                $successDelivery
            )
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->extraAttributes([
                    'class' => 'bg-green-200 shadow-md rounded-lg p-4',
                ]),

            Stat::make(
                'Cash Collected',
                number_format($collectedCashSales, 2).' EGP'
            )
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->extraAttributes([
                    'class' => 'bg-emerald-100 shadow-md rounded-lg p-4 cash-collected-stat',
                ]),
        ]);
    }
}
