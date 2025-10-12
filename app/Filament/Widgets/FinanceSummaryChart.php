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

        // ✅ Base query
        $ordersQuery = Order::query()
            ->where('is_collected', 0);

        // ✅ Restrict to logged-in user's data if not admin
        if (! $user->isAdmin()) {
            $ordersQuery->where('users_id', $user->id);
            $this->viewType = 'My Orders View';
        } else {
            $this->viewType = 'Admin View (All Orders)';
        }

        // ✅ Define statuses
        $collectedCashStatuses = ['success_delivery', 'partial_return'];
        $shippingFeeStatuses = ['success_delivery', 'partial_return', 'undelivered'];

        // 1️⃣ Collected Cash (COD)
        $collectedCash = (clone $ordersQuery)
            ->whereIn('status', $collectedCashStatuses)
            ->sum('cod_amount');

        // 2️⃣ Shipping Fees (delivery_cost)
        $shippingFees = (clone $ordersQuery)
            ->whereIn('status', $shippingFeeStatuses)
            ->sum('delivery_cost');

        // 3️⃣ Open Package Fees
        $openPackageFees = (clone $ordersQuery)->sum('open_package_fee');

        // ✅ Combine Shipping + Open Package Fees
        $totalFees = $shippingFees + $openPackageFees;

        // 4️⃣ Net Profit = Collected Cash - (Shipping + Open Package Fees)
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
                        'rgba(40, 167, 69, 0.5)',   // ✅ Collected Cash - green
                        'rgba(255, 193, 7, 0.5)',   // ✅ Total Fees (Shipping + Open Package) - yellow
                        'rgba(0, 123, 255, 0.5)',   // ✅ Net Profit - blue
                    ],
                    'borderColor' => [
                        'rgba(40, 167, 69, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(0, 123, 255, 1)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => ['Collected Cash', 'Total Fees', 'Net Profit'],
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
