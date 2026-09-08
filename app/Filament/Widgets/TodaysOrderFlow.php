<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\LineChartWidget;
use Illuminate\Support\Facades\Auth;

class TodaysOrderFlow extends LineChartWidget
{
    protected static ?string $heading = 'Order Flow';

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
            ->selectRaw('HOUR(updated_at) as hour, COUNT(*) as total')
            ->groupBy('hour')
            ->pluck('total', 'hour');

        $labels = [];
        $data = [];

        // Show the full day, hour by hour
        foreach (range(0, 23) as $hour) {
            $labels[] = date('g A', strtotime(sprintf('%02d:00', $hour)));
            $data[] = $orders[$hour] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $data,

                    // Lynk primary blue
                    'borderColor' => '#2563EB',
                    'backgroundColor' => 'rgba(37, 99, 235, 0.2)',
                    'fill' => true,

                    'tension' => 0.4,

                    'pointBackgroundColor' => '#2563EB',
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
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
