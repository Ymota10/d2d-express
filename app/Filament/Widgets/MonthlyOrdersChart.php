<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\LineChartWidget;
use Illuminate\Support\Facades\Auth;

class MonthlyOrdersChart extends LineChartWidget
{
    protected static ?string $heading = 'Orders per month';

    public function getColumnSpan(): int|string|array
    {
        return 1;
    }

    protected function getExtraAttributes(): array
    {
        return [
            'class' => 'h-80',
        ];
    }

    protected function getData(): array
    {
        $query = Order::query()
            ->whereYear('created_at', now()->year);

        // Shipper sees only his own orders
        if (Auth::user()->management === 'shipper') {
            $query->where('users_id', Auth::id());
        }

        $orders = $query
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->pluck('total', 'month');

        $labels = [];
        $data = [];

        foreach (range(1, 12) as $month) {
            $labels[] = Carbon::create()->month($month)->format('M');
            $data[] = $orders[$month] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $data,

                    // New primary blue
                    'borderColor' => '#2563EB',

                    // Lighter version of the new primary blue
                    'backgroundColor' => 'rgba(37, 99, 235, 0.20)',

                    'fill' => true,

                    'tension' => 0.4,

                    // New primary blue
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

            'elements' => [
                'line' => [
                    'borderWidth' => 1.5,
                    'borderJoinStyle' => 'round',

                    // New primary blue shadow
                    'shadowColor' => 'rgba(37, 99, 235, 0.5)',

                    'shadowBlur' => 30,
                ],
            ],
        ];
    }
}
