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

        /*
         * ============================================================
         * GET TODAY'S ORDER COUNTS
         * ============================================================
         *
         * Partial Return is intentionally NOT displayed separately.
         * It will be added to Success Delivery below.
         */

        $orders = $query
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        /*
         * ============================================================
         * SUCCESS DELIVERY
         * ============================================================
         *
         * Success Delivery + Partial Return
         */

        $successDelivery = ($orders['success_delivery'] ?? 0)
            + ($orders['partial_return'] ?? 0);

        /*
         * ============================================================
         * STATUS DATA
         * ============================================================
         */

        $statusData = [
            'pickup_request' => [
                'label' => 'Pickup Request',
                'count' => $orders['pickup_request'] ?? 0,
                'color' => '#FFD700', // Yellow
            ],

            'warehouse_received' => [
                'label' => 'Warehouse Received',
                'count' => $orders['warehouse_received'] ?? 0,
                'color' => '#2563EB', // Blue
            ],

            'out_for_delivery' => [
                'label' => 'OFD',
                'count' => $orders['out_for_delivery'] ?? 0,
                'color' => '#90EE90', // Light Green
            ],

            'success_delivery' => [
                'label' => 'Success Delivery',
                'count' => $successDelivery,
                'color' => '#15803D', // Dark Green
            ],

            'undelivered' => [
                'label' => 'Undelivered',
                'count' => $orders['undelivered'] ?? 0,
                'color' => '#DC2626', // Red
            ],

            'time_scheduled' => [
                'label' => 'Time Scheduled',
                'count' => $orders['time_scheduled'] ?? 0,
                'color' => '#800080', // Purple
            ],

            'returned_to_warehouse' => [
                'label' => 'Returned to Warehouse',
                'count' => $orders['returned_to_warehouse'] ?? 0,
                'color' => '#87CEFA', // Light Blue
            ],
        ];

        /*
         * ============================================================
         * BUILD CHART DATA
         * ============================================================
         */

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($statusData as $status) {
            if ($status['count'] > 0) {
                $labels[] = $status['label'];
                $data[] = $status['count'];
                $colors[] = $status['color'];
            }
        }

        return [
            'labels' => $labels,

            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $data,
                    'backgroundColor' => $colors,
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
