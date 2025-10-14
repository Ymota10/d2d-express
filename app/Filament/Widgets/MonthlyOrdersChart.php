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
        return 1; // ✅ Each widget takes 1 column (since dashboard uses 2)
    }

    protected function getExtraAttributes(): array
    {
        return [
            'class' => 'h-80', // ✅ Consistent height across dashboard
        ];
    }

    protected function getData(): array
    {
        $query = Order::query()
            ->whereYear('created_at', now()->year);

        // ✅ Shipper sees only his own orders
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
                    'borderColor' => '#02447d', // primary blue
                    'backgroundColor' => 'rgba(3, 105, 193, 0.2)', // lighter blue fill
                    'fill' => true,
                    'tension' => 0.4,
                    'pointBackgroundColor' => '#02447d',
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
                'duration' => 1500, // ms
                'easing' => 'easeInOutQuart', // smooth easing
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
                    'shadowColor' => 'rgba(2, 68, 125, 0.5)',
                    'shadowBlur' => 30,
                ],
            ],
        ];
    }
}
