<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->passwordReset()
            ->colors([
                'primary' => '#02447d',
                'secondary' => '#800080',
                'tertiary' => '#000000',
                'third' => '#90EE90',
                'fourth' => '#011414',
                'fifth' => '#00A4BF',
                'six' => '#1271FF',
                'brown' => '#964B00',
                'lime' => '#50C878',
                'Darkgreen' => '#008000',
                'neon' => '#0FFF50',
            ])
            ->discoverResources(
                in: app_path('Filament/Resources'),
                for: 'App\\Filament\\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/Pages'),
                for: 'App\\Filament\\Pages'
            )
            ->pages([
                Pages\Dashboard::class, // Keep the dashboard page registered
            ])
            // Discover all widgets so Livewire can find them
            ->discoverWidgets(
                in: app_path('Filament/Widgets'),
                for: 'App\\Filament\\Widgets'
            )
            // Specify **only the dashboard widgets you want in this order**
            ->widgets([
                \App\Filament\Widgets\Cards::class,
                \App\Filament\Widgets\MonthlyOrdersChart::class,
                \App\Filament\Widgets\MonthlyShippersChart::class,
                \App\Filament\Widgets\UnsuccessfulReasonsWidget::class,
                \App\Filament\Widgets\DeliveryRateChart::class,
                \App\Filament\Widgets\FinanceSummaryChart::class,
                \App\Filament\Widgets\AverageDeliveryTimeChart::class,
                \App\Filament\Widgets\CitySuccessRateWidget::class,
            ])
            ->brandLogo(asset('images/d2d_MAIN_LOGO-removebg-preview.png'))
            ->brandLogoHeight('150px')
            ->favicon('images/d2d MAIN LOGO.jpg')
            ->sidebarCollapsibleOnDesktop()
            ->navigationItems([
                NavigationItem::make('Our Website')
                    ->url('https://d2d-express-eg.com', shouldOpenInNewTab: true)
                    ->icon('heroicon-o-tv')
                    ->group('External')
                    ->sort(10),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
