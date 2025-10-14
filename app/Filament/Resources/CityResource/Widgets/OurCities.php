<?php

namespace App\Filament\Resources\CityResource\Widgets;

use App\Models\City;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OurCities extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Cities', City::count())
                ->description('All Registered Cities')
                ->descriptionColor('primary')
                ->icon('fluentui-globe-location-24-o'),

        ];
    }

    protected int|string|array $columnSpan = 'full'; // ✅ spans 1 column
}
