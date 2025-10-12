<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class TrackOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected static ?string $title = 'Order Tracking';

    protected static string $view = 'filament.resources.order-resource.pages.track-order';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Back')
                ->color('gray')
                ->url(OrderResource::getUrl('index')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\OrderStatusTimelineWidget::class,
        ];
    }

    public function getHeading(): string
    {
        return 'Tracking for Order #'.$this->record->waybill_number;
    }
}
