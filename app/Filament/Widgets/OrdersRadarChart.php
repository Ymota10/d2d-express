<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrdersRadarChart extends ChartWidget
{
    protected static ?string $heading = 'Orders Radar';

    protected function getData(): array
    {
        $shippers = Order::with('shipper')->get()->groupBy('shipper.name');

        $areas = Order::with('area')->get()->groupBy('area.name');

        $labels = []; // x-axis labels
        $seriesData = []; // y-axis values

        foreach ($shippers as $shipperName => $orders) {
            $labels[] = $shipperName;
            $seriesData[] = $orders->count();
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Order Count per Shipper',
                    'data' => $seriesData,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'radar';
    }
}
