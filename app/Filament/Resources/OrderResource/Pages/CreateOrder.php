<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Services\BostaService;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $order = $this->record;
        try {
            (new BostaService)->createShipment($order);
        } catch (\Throwable $e) {
            \Log::error('Bosta sync failed', ['error' => $e->getMessage()]);
        }
    }
}
