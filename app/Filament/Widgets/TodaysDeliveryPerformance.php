<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\DoughnutChartWidget;
use Illuminate\Support\Facades\Auth;

class TodaysDeliveryPerformance extends DoughnutChartWidget
{
    protected static ?string $heading = 'Delivery Performance';

    protected function getExtraAttributes(): array
    {
        return [
            'class' => 'h-80',
        ];
    }

    public function getColumnSpan(): int|string|array
    {
        return 1;
    }

    protected function getData(): array
    {
        $query = Order::query()
            ->whereDate('updated_at', today());

        // Shipper sees only his own orders
        if (Auth::user()->management === 'shipper') {
            $query->where('users_id', Auth::id());
        }

        $orders = $query
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusLabels = [
            'pickup_request' => 'Pickup Request',
            'warehouse_received' => 'Warehouse',
            'out_for_delivery' => 'OFD',
            'success_delivery' => 'Success',
            'partial_return' => 'Partial',
            'time_scheduled' => 'Time Scheduled',
            'failed_attempt' => 'Failed Attempt',
            'undelivered' => 'Undelivered',
            'returned_to_warehouse' => 'Returning',
        ];

        $labels = [];
        $data = [];

        foreach ($statusLabels as $status => $label) {
            $count = $orders[$status] ?? 0;

            if ($count > 0) {
                $labels[] = $label;
                $data[] = $count;
            }
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $data,
                    'backgroundColor' => [
                        '#2563EB', // Lynk Blue
                        '#1271FF',
                        '#29AB87',
                        '#50C878',
                        '#FC6A03',
                        '#FFD700',
                        '#FF8C00',
                        '#d1001f',
                        '#800080',
                        '#964B00',
                        '#808080',
                    ],
                    'borderWidth' => 1,
                ],
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,

            'animation' => [
                'duration' => 1500,
                'easing' => 'easeInOutQuart',
            ],

            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],

            'scales' => [
                'x' => [
                    'ticks' => [
                        'maxRotation' => 45,
                        'minRotation' => 0,
                    ],
                ],

                'y' => [
                    'beginAtZero' => true,

                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],

            'elements' => [
                'line' => [
                    'borderWidth' => 1.5,
                    'borderJoinStyle' => 'round',
                ],
            ],
        ];
    }
}
