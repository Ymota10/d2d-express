<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;

class UsersBarChart extends ChartWidget
{
    protected static ?string $heading = 'Users Count';

    protected function getData(): array
    {
        $users = User::select('name')->get();

        return [
            'labels' => $users->pluck('name')->toArray(),
            'datasets' => [
                [
                    'label' => 'User Count',
                    'data' => array_fill(0, $users->count(), 1), // Each user gets a count of 1
                    'backgroundColor' => 'rgba(3, 105, 193, 0.7)', // soft blue
                    'borderColor' => '#0369c1', // strong blue
                    'borderWidth' => 2,
                    'hoverBackgroundColor' => '#0369c1',
                    'hoverBorderColor' => '#023e7d',
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
