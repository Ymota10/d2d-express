<?php

namespace App\Filament\Resources\CourierResource\Widgets;

use App\Models\Courier;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OurCouriers extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Couriers', Courier::count())
                ->description('All Registered Couriers')
                ->descriptionColor('primary')
                ->icon('healthicons-o-truck-driver'),

        ];
    }

    protected int|string|array $columnSpan = 'full'; // ✅ spans 1 column
}
