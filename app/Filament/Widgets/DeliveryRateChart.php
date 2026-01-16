<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\HtmlString;

class DeliveryRateChart extends ChartWidget
{
    protected static ?string $heading = 'Delivery Success Rate';

    private float $deliveryRate = 0;

    public function getHeading(): string|HtmlString
    {
        $colorClass = $this->deliveryRate >= 70
            ? 'bg-green-600 dark:bg-green-500'
            : 'bg-red-600 dark:bg-red-500';

        return new HtmlString('
            <div class="flex flex-col items-center justify-center space-y-2">
                <span class="text-lg font-semibold">'.static::$heading.'</span>
                <span class="inline-block px-3 py-1 text-sm font-bold rounded-full shadow-md 
                    '.$colorClass.' 
                    !text-white">
                    '.$this->deliveryRate.'% Success
                </span>
            </div>
        ');
    }

    protected function getData(): array
    {
        $user = auth()->user();

        $ordersQuery = Order::query();

        if (! $user->isAdmin()) {
            $ordersQuery->where('users_id', $user->id);
        }

        // ✅ Successful
        $successDelivery = (clone $ordersQuery)
            ->where('status', 'success_delivery')
            ->count();

        $partialReturn = (clone $ordersQuery)
            ->where('status', 'partial_return')
            ->count();

        // ❌ Unsuccessful
        $undelivered = (clone $ordersQuery)
            ->where('status', 'undelivered')
            ->count();

        $returnedCostPaid = (clone $ordersQuery)
            ->where('status', 'returned_and_cost_paid')
            ->count();

        $successfulOrders = $successDelivery + $partialReturn;
        $unsuccessfulOrders = $undelivered + $returnedCostPaid;
        $closedOrders = $successfulOrders + $unsuccessfulOrders;

        $this->deliveryRate = $closedOrders > 0
            ? round(($successfulOrders / $closedOrders) * 100, 2)
            : 0;

        return [
            'datasets' => [
                [
                    'data' => [
                        $successDelivery,
                        $partialReturn,
                        $undelivered,
                        $returnedCostPaid,
                    ],
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.6)',  // success_delivery  ✅ (كان partial)
                        'rgba(0, 132, 80, 0.6)',   // partial_return    ✅ (كان success)
                        'rgba(220, 38, 38, 0.6)',  // undelivered       ✅ (كان returned)
                        'rgba(239, 68, 68, 0.6)',  // returned_and_cost_paid ✅ (كان undelivered)
                    ],
                    'borderColor' => [
                        'rgba(34, 197, 94, 1)',
                        'rgba(0, 132, 80, 1)',
                        'rgba(220, 38, 38, 1)',
                        'rgba(239, 68, 68, 1)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => [
                'Success Delivery',
                'Partial Return',
                'Undelivered',
                'Returned (Cost Paid)',
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
            'cutout' => '70%',
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
