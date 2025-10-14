<?php

namespace App\Filament\Pages;

use App\Models\Order;
use Filament\Pages\Page;

class CheckShipments extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Scanning';

    protected static string $view = 'filament.pages.check-shipments';

    public $orders = [];

    public $viewingOrder = null;

    public function mount()
    {
        $ids = explode(',', request('order_id'));
        $this->orders = Order::with(['user', 'area'])->whereIn('id', $ids)->get();
    }

    public function remove($id)
    {
        $this->orders = collect($this->orders)
            ->reject(fn ($order) => $order->id == $id)
            ->values();
    }

    public function viewOrder($id)
    {
        $this->viewingOrder = Order::with(['user', 'area'])->find($id);
    }

    public function bulkDelete()
    {
        $this->orders = collect();
    }
}
