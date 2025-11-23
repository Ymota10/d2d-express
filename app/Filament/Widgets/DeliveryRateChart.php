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

        $inProgressStatuses = [
            'out_for_delivery',
            'returned_to_warehouse',
            'time_scheduled',
            'warehouse_recieved',
            'pickup_request',
        ];

        // ✅ Start query
        $ordersQuery = Order::query();

        // ✅ Non-admin users only see their own orders
        if (! $user->isAdmin()) {
            $ordersQuery->where('users_id', $user->id);
        }

        // ✅ Count successful and unsuccessful deliveries
        $successfulOrders = (clone $ordersQuery)
            ->whereIn('status', ['success_delivery', 'partial_return'])
            ->whereNotIn('status', $inProgressStatuses)
            ->count();

        $unsuccessfulOrders = (clone $ordersQuery)
            ->whereIn('status', ['undelivered', 'returned_and_cost_paid'])
            ->whereNotIn('status', $inProgressStatuses)
            ->count();

        $closedOrders = $successfulOrders + $unsuccessfulOrders;

        $this->deliveryRate = $closedOrders > 0
            ? round(($successfulOrders / $closedOrders) * 100, 2)
            : 0;

        return [
            'datasets' => [
                [
                    'data' => [$successfulOrders, $unsuccessfulOrders],
                    'backgroundColor' => [
                        'rgba(0, 132, 80, 0.5)',   // ✅ shaded green
                        'rgba(239, 68, 68, 0.5)', // ✅ shaded red
                    ],
                    'borderColor' => [
                        'rgba(0, 132, 80, 1)',
                        'rgba(239, 68, 68, 1)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => ['Successful', 'Unsuccessful'],
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
