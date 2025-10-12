<?php

namespace App\Filament\Widgets;

use App\Models\Branches;
use Filament\Widgets\ChartWidget;

class BranchesLineChart extends ChartWidget
{
    protected static ?string $heading = 'Branches';

    protected function getData(): array
    {
        // Fetch all branches with their user count
        $branches = Branches::withCount('user')->get();

        return [
            'labels' => $branches->pluck('name')->toArray(),
            'datasets' => [
                [
                    'label' => 'Users Count',
                    'data' => $branches->pluck('users_count')->toArray(),
                    'borderColor' => '#0369c1',
                    'backgroundColor' => '#0369c1',
                    'tension' => 0.4,
                    'fill' => true,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
