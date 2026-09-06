<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Contracts\Support\Htmlable;

class Analytics extends Page
{
    protected static string $routePath = 'analytics';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Analytics';

    protected static ?string $title = 'Analytics';

    protected static string $view = 'filament-panels::pages.dashboard';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->management !== 'track_express';
    }

    public static function getNavigationLabel(): string
    {
        return static::$navigationLabel ?? static::$title ?? 'Analytics';
    }

    public static function getNavigationIcon(): string|Htmlable|null
    {
        return static::$navigationIcon
            ?? FilamentIcon::resolve('panels::pages.dashboard.navigation-item')
            ?? 'heroicon-o-chart-bar';
    }

    public static function getRoutePath(): string
    {
        return static::$routePath;
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\MonthlyOrdersChart::class,
            \App\Filament\Widgets\MonthlyShippersChart::class,
            \App\Filament\Widgets\UnsuccessfulReasonsWidget::class,
            \App\Filament\Widgets\DeliveryRateChart::class,
            \App\Filament\Widgets\FinanceSummaryChart::class,
            \App\Filament\Widgets\AverageDeliveryTimeChart::class,
            \App\Filament\Widgets\CitySuccessRateWidget::class,
        ];
    }

    public function getVisibleWidgets(): array
    {
        return $this->filterVisibleWidgets($this->getWidgets());
    }

    public function getColumns(): int|string|array
    {
        return 2;
    }

    public function getTitle(): string|Htmlable
    {
        return static::$title ?? 'Analytics';
    }
}
