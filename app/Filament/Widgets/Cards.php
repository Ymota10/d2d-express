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

        /*
         * In Progress:
         *
         * time_scheduled
         * returned_to_warehouse
         * failed_attempt
         */

        $inProgress = (clone $orderQuery)
            ->whereIn('status', [
                'time_scheduled',
                'returned_to_warehouse',
                'failed_attempt',
            ])
            ->count();

        /*
         * Success Delivery:
         *
         * success_delivery
         * partial_return
         */

        $successDelivery = (clone $orderQuery)
            ->whereIn('status', [
                'success_delivery',
                'partial_return',
            ])
            ->count();

        /*
         * Undelivered:
         *
         * undelivered
         * returned_and_cost_paid
         */

        $undelivered = (clone $orderQuery)
            ->whereIn('status', [
                'undelivered',
                'returned_and_cost_paid',
            ])
            ->count();

        /*
         * ============================================================
         * RETURN CARDS
         * ============================================================
         */

        return array_filter([

            // ========================================================
            // ADMIN ONLY
            // ========================================================

            $user?->isAdmin()
                ? Stat::make(
                    'Total Couriers',
                    User::where('management', 'courier')->count()
                )
                    ->description('5% increase')
                    ->descriptionIcon('heroicon-m-arrow-trending-up')
                    ->color('success')
                    ->chart([37, 30, 32, 35, 34, 40, 42])
                    ->icon('healthicons-o-truck-driver')
                : null,

            $user?->isAdmin()
                ? Stat::make(
                    'Total Cities',
                    City::count()
                )
                    ->description('3% decrease')
                    ->descriptionIcon('heroicon-m-arrow-trending-down')
                    ->color('danger')
                    ->chart([37, 30, 32, 35, 34, 40, 42])
                    ->icon('fluentui-globe-location-24-o')
                : null,

            $user?->isAdmin()
                ? Stat::make(
                    'Total Areas',
                    Area::count()
                )
                    ->description('7% increase')
                    ->descriptionIcon('heroicon-m-arrow-trending-up')
                    ->color('success')
                    ->chart([37, 30, 32, 35, 34, 40, 42])
                    ->icon('fluentui-globe-surface-20-o')
                : null,

            // ========================================================
            // TODAY'S ORDER STATUS CARDS
            // ========================================================

            Stat::make(
                'Pickup Requests',
                $pickupRequest
            )
                // ->description('Updated today')
                ->icon('heroicon-o-hand-raised')
                ->color('warning')
                ->extraAttributes([
                    'class' => 'bg-blue-100 shadow-md rounded-lg p-4',
                ]),

            Stat::make(
                'Warehouse Received',
                $warehouseReceived
            )
                // ->description('Updated today')
                ->icon('heroicon-o-home-modern')
                ->color('info')
                ->extraAttributes([
                    'class' => 'bg-indigo-100 shadow-md rounded-lg p-4',
                ]),

            Stat::make(
                'Out For Delivery',
                $outForDelivery
            )
                // ->description('Updated today')
                ->icon('heroicon-o-truck')
                ->color('success')
                ->extraAttributes([
                    'class' => 'bg-green-100 shadow-md rounded-lg p-4',
                ]),

            Stat::make(
                'In Progress',
                $inProgress
            )
                // ->description('Updated today')
                ->icon('heroicon-m-chevron-double-up')
                ->color('gray')
                ->extraAttributes([
                    'class' => 'bg-purple-100 shadow-md rounded-lg p-4',
                ]),

            Stat::make(
                'Success Delivery',
                $successDelivery
            )
                // ->description('Updated today')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->extraAttributes([
                    'class' => 'bg-green-200 shadow-md rounded-lg p-4',
                ]),

            Stat::make(
                'Undelivered',
                $undelivered
            )
                // ->description('Updated today')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->extraAttributes([
                    'class' => 'bg-red-100 shadow-md rounded-lg p-4',
                ]),
        ]);
    }
}
