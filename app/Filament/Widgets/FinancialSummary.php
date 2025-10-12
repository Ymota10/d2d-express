<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class FinancialSummary extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();

        // Base query depending on management role
        $ordersQuery = Order::query();

        if ($user->isShipper()) {
            $ordersQuery->where('users_id', $user->id);
        }

        // COLLECTED COD only for success_delivery orders AND is_collected = 0
        $totalCodAmount = (clone $ordersQuery)
            ->where('status', 'success_delivery')
            ->where('is_collected', 0)
            ->sum('cod_amount');

        // D2D REVENUE (before courier commission) AND is_collected = 0
        $d2dRevenue = (clone $ordersQuery)
            ->whereIn('status', ['success_delivery', 'undelivered'])
            ->where('is_collected', 0)
            ->sum('delivery_cost');

        // OPEN PACKAGE FEES AND is_collected = 0
        $openPackageTotal = (clone $ordersQuery)
            ->where('open_package', 'yes')
            ->whereIn('status', ['success_delivery', 'undelivered'])
            ->where('is_collected', 0)
            ->sum('open_package_fee');

        // NET DESERVED = COLLECTED COD - D2D REVENUE - OPEN PACKAGE FEES
        $netDeserved = $totalCodAmount - $d2dRevenue - $openPackageTotal;

        $stats = [
            Stat::make('DELIVERED', (clone $ordersQuery)
                ->where('status', 'success_delivery')
                ->where('is_collected', 0)
                ->count())
                ->description('Success Delivery!')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([20, 23, 26, 30, 35, 39, 33])
                ->icon('heroicon-o-hand-thumb-up'),

            Stat::make('RETURNED', (clone $ordersQuery)
                ->where('status', 'partial_return')
                ->where('is_collected', 0)
                ->count())
                ->description('Uh Oh!')
                ->descriptionIcon('heroicon-o-arrow-trending-down')
                ->color('warning')
                ->chart([20, 23, 26, 30, 35, 39, 33])
                ->icon('heroicon-o-hand-thumb-down'),

            Stat::make('REPLACEMENT', (clone $ordersQuery)
                ->where('service_type', 'replacement')
                ->where('is_collected', 0)
                ->count())
                ->description('Re-shipping! (INCLUSIVE IN DELIVERED CARD)')
                ->descriptionIcon('heroicon-m-arrows-right-left')
                ->color('warning')
                ->chart([20, 23, 26, 30, 35, 39, 33])
                ->icon('heroicon-s-arrow-path'),

            Stat::make('UNDELIVERED', (clone $ordersQuery)
                ->where('status', 'undelivered')
                ->where('is_collected', 0)
                ->count())
                ->description('Failed Delivery')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger')
                ->chart([12, 18, 15, 20, 22, 19, 17])
                ->icon('heroicon-o-exclamation-triangle'),

            Stat::make('COLLECTED COD', number_format($totalCodAmount, 2))
                ->description('Revenue')
                ->color('lime')
                ->chart([20, 23, 26, 30, 35, 39, 33])
                ->icon('heroicon-o-credit-card'),

            Stat::make('NET DESERVED', number_format($netDeserved, 2))
                ->description('Net Amount To be Transferred')
                ->color('success')
                ->chart([20, 23, 26, 30, 35, 39, 33])
                ->icon('heroicon-o-currency-dollar'),

            Stat::make('Delivery Fees', number_format($d2dRevenue, 2))
                ->description('Total Delivery Cost')
                ->color('primary')
                ->chart([20, 23, 26, 30, 35, 39, 33])
                ->icon('heroicon-s-arrow-left-circle'),

            Stat::make('OPEN PACKAGE FEES', number_format($openPackageTotal, 2))
                ->description('Additional 5 EGP per open package')
                ->color('info')
                ->chart([10, 12, 15, 18, 20, 22, 25])
                ->icon('heroicon-o-gift'),
        ];

        // ✅ Add D2D Net Income (Admin only)
        if ($user->isAdmin()) {
            $d2dNetIncome = (clone $ordersQuery)
                ->where(function ($query) {
                    $query->whereIn('status', ['success_delivery', 'undelivered'])
                        ->orWhere('service_type', 'replacement');
                })
                ->where('is_collected', 0)
                ->with(['user.branch.shippingFees'])
                ->get()
                ->sum(function ($order) {
                    $branch = $order->user?->branch;
                    if (! $branch) {
                        return 0;
                    }

                    $fee = $branch->shippingFees
                        ->where('city_id', $order->city_id)
                        ->first();
                    if (! $fee) {
                        return 0;
                    }

                    if ($order->service_type === 'replacement') {
                        $cost = $fee->exchange_cost;
                    } elseif ($order->status === 'undelivered') {
                        $cost = $fee->return_cost;
                    } else {
                        $cost = $fee->delivery_cost;
                    }

                    return $cost > 0 ? ($order->delivery_cost - $cost) : 0;
                });

            $stats[] = Stat::make('D2D NET INCOME', number_format($d2dNetIncome, 2))
                ->description('After Courier Commission')
                ->color('primary')
                ->chart([20, 23, 26, 30, 35, 39, 33])
                ->icon('heroicon-s-arrow-right-circle');
        }

        return $stats;
    }
}
