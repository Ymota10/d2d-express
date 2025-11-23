<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\HtmlString;

class AverageDeliveryTimeChart extends ChartWidget
{
    protected static ?string $heading = 'Average Delivery Time';

    private float $averageHours = 0;

    public function getHeading(): string|HtmlString
    {
        if ($this->averageHours === 0) {
            return new HtmlString('<div class="flex flex-col items-center justify-center space-y-2">
                <span class="text-lg font-semibold">'.static::$heading.'</span>
                <span class="inline-block px-3 py-1 text-sm font-bold rounded-full shadow-md 
                    bg-gray-500 
                    !text-black dark:!text-white">
                    No Data
                </span>
            </div>');
        }

        $days = floor($this->averageHours / 24);
        $hours = (int) ($this->averageHours % 24);

        return new HtmlString('
            <div class="flex flex-col items-center justify-center space-y-2">
                <span class="text-lg font-semibold">'.static::$heading.'</span>
                <span class="inline-block px-3 py-1 text-sm font-bold rounded-full shadow-md 
                    bg-blue-600 
                    !text-black dark:!text-white">
                    '.$days.' Day'.($days !== 1 ? 's' : '').', '.$hours.' H
                </span>
            </div>
        ');
    }

    protected function getData(): array
    {
        // ✅ Fetch successful deliveries only
        $successfulOrders = Order::where('status', 'success_delivery')
            ->whereNotNull('created_at')
            ->whereNotNull('updated_at')
            ->get();

        if ($successfulOrders->isEmpty()) {
            $this->averageHours = 0;

            return [
                'datasets' => [
                    [
                        'data' => [1],
                        'backgroundColor' => ['rgba(229, 231, 235, 0.5)'],
                        'borderWidth' => 0,
                    ],
                ],
                'labels' => ['No Data'],
            ];
        }

        // Calculate average actual delivery time
        $this->averageHours = (float) $successfulOrders
            ->map(fn ($order) => $order->created_at->diffInHours($order->updated_at))
            ->avg();

        // ✅ CAP: Never exceed 55 hours (2 days + 7 hours)
        $this->averageHours = min($this->averageHours, 55);

        // Convert to days & cap at 5 for donut segments
        $avgDays = min($this->averageHours / 24, 5.0);

        // ✅ Segment breakdown
        $greenAmount = round(min($avgDays, 2.0), 6);                  // 0–2 days
        $yellowAmount = round(max(min($avgDays - 2.0, 2.0), 0.0), 6); // 2–4 days
        $redAmount = round(max($avgDays - 4.0, 0.0), 6);              // 4–5 days

        $data = [];
        $bg = [];
        $border = [];
        $labels = [];

        if ($greenAmount > 0) {
            $data[] = $greenAmount;
            $bg[] = 'rgba(40, 167, 69, 0.7)';  // shaded green
            $border[] = 'rgba(40, 167, 69, 1)';
            $labels[] = '0–2 Days';
        }

        if ($yellowAmount > 0) {
            $data[] = $yellowAmount;
            $bg[] = 'rgba(255, 193, 7, 0.7)';  // shaded yellow
            $border[] = 'rgba(255, 193, 7, 1)';
            $labels[] = '2–4 Days';
        }

        if ($redAmount > 0) {
            $data[] = $redAmount;
            $bg[] = 'rgba(220, 53, 69, 0.7)';  // shaded red
            $border[] = 'rgba(220, 53, 69, 1)';
            $labels[] = '4–5+ Days';
        }

        if (empty($data)) {
            return [
                'datasets' => [
                    [
                        'data' => [1],
                        'backgroundColor' => ['rgba(229, 231, 235, 0.5)'],
                        'borderWidth' => 0,
                    ],
                ],
                'labels' => ['No Data'],
            ];
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $bg,
                    'borderColor' => $border,
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'animation' => [
                'animateRotate' => true,
                'animateScale' => true,
                'duration' => 1200,
                'easing' => 'easeInOutQuart',
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'labels' => [
                        // Light Mode
                        'color' => 'black',
                    ],
                ],
                'tooltip' => [
                    'enabled' => true,
                    // Light mode text
                    'titleColor' => 'black',
                    'bodyColor' => 'black',

                    // Dark mode text (Tailwind dark)
                    'titleColor' => 'function() {
                        return document.documentElement.classList.contains("dark") ? "#fff" : "#000";
                    }',
                    'bodyColor' => 'function() {
                        return document.documentElement.classList.contains("dark") ? "#fff" : "#000";
                    }',

                    'callbacks' => [
                        'label' => 'function(context) {
                            var value = context.raw ?? 0;
                            var total = context.dataset.data.reduce(function(acc, v){ return acc + v; }, 0);
                            var pct = total > 0 ? (value / total * 100).toFixed(1) : 0;
                            return context.label + ": " + value.toFixed(2) + " days (" + pct + "%)";
                        }',
                    ],
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
