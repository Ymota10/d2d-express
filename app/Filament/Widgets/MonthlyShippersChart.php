<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\LineChartWidget;

class MonthlyShippersChart extends LineChartWidget
{
    protected static ?string $heading = 'Total Shippers';

    // ✅ Only allow admins to view this widget
    public static function canView(): bool
    {
        $user = auth()->user();

        return $user && $user->isAdmin();
    }

    protected function getData(): array
    {
        $shippers = User::where('management', 'shipper')
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->pluck('total', 'month');

        $labels = [];
        $data = [];
        $cumulative = 0;

        foreach (range(1, 12) as $month) {
            $cumulative += $shippers[$month] ?? 0;
            $labels[] = Carbon::create()->month($month)->format('M');
            $data[] = $cumulative;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Shippers',
                    'data' => $data,
                    'borderColor' => '#02447d', // primary blue
                    'backgroundColor' => 'rgba(3, 105, 193, 0.2)', // lighter blue fill
                    'fill' => true,
                    'tension' => 0.4,
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
                    'borderWidth' => 2.5,
                    'borderJoinStyle' => 'round',
                    'shadowColor' => 'rgba(2, 68, 125, 0.5)',
                    'shadowBlur' => 30,
                ],
            ],
        ];
    }
}
