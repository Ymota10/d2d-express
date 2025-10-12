<?php

namespace App\Filament\Resources\AreaResource\Widgets;

use App\Models\Area;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OurAreas extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Areas', Area::count())
                ->description('All Registered Areas')
                ->descriptionColor('primary')
                ->icon('fluentui-globe-surface-20-o'),

        ];
    }

    protected int|string|array $columnSpan = 'full'; // ✅ spans 1 column
}
