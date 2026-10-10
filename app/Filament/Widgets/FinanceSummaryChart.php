<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class FinanceSummaryChart extends ChartWidget
{
    protected static ?string $heading = 'Cash Cycles';

    private string $viewType = '';

    protected function getData(): array
    {
        $user = auth()->user();

        // Base query: only orders not yet collected
        $ordersQuery = Order::query()
            ->where('is_collected', 0);

        // Restrict non-admin users to their own orders
        if (! $user->isAdmin()) {
            $ordersQuery->where('users_id', $user->id);
            $this->viewType = 'My Orders View';
        } else {
            $this->viewType = 'Admin View (All Orders)';
        }

        // Define statuses
        $collectedCashStatuses = [
            'success_delivery',
            'partial_return',
        ];

        $shippingFeeStatuses = [
            'success_delivery',
            'partial_return',
            'undelivered',
        ];

        // 1. Collected Cash (COD)
        $collectedCash = (clone $ordersQuery)
            ->whereIn('status', $collectedCashStatuses)
            ->sum('cod_amount');

        // 2. Shipping Fees:
        // Undelivered = return_cost
        // Successful/partial delivery = delivery_cost
        $shippingFees = (clone $ordersQuery)
            ->whereIn('status', $shippingFeeStatuses)
            ->get()
            ->sum(function ($order) {
                return $order->status === 'undelivered'
                    ? $order->return_cost
                    : $order->delivery_cost;
            });

        // 3. Open Package Fees
        $openPackageFees = (clone $ordersQuery)
            ->whereIn('status', $shippingFeeStatuses)
            ->where('open_package', 'yes')
            ->sum('open_package_fee');

        // 4. Total Fees
        $totalFees = $shippingFees;

        // 5. Net Profit
        $netProfit = $collectedCash - $totalFees;

        return [
            'datasets' => [
                [
                    'label' => 'Finance Breakdown',
                    'data' => [
                        $collectedCash,
                        $totalFees,
                        $netProfit,
                    ],
                    'backgroundColor' => [
                        'rgba(40, 167, 69, 0.5)',
                        'rgba(255, 193, 7, 0.5)',
                        'rgba(0, 123, 255, 0.5)',
                    ],
                    'borderColor' => [
                        'rgba(40, 167, 69, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(0, 123, 255, 1)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => [
                'Collected Cash',
                'Total Fees',
                'Net Profit',
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'animation' => [
                'animateRotate' => true,
                'animateScale' => true,
                'duration' => 1500,
                'easing' => 'easeInOutQuart',
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'polarArea'; // You can switch to 'doughnut' or 'bar'
    }
}
