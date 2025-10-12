<?php

namespace App\Filament\Pages;

use App\Models\Order;
use Filament\Forms\Components\Textarea;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class BarcodeScan extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-qr-code';

    protected static string $view = 'filament.pages.barcode-scan';

    protected static ?string $navigationGroup = 'Scanning';

    protected static ?string $navigationLabel = 'Barcode Scan';

    public $waybills = '';      // raw input

    public $orders = [];        // fetched orders

    public $duplicates = [];    // list of duplicates

    /** Search for waybills in Orders table */
    public function search(): void
    {
        $codes = collect(preg_split('/[\s,]+/', trim($this->waybills)))
            ->filter()
            ->map(fn ($code) => strtoupper(trim($code)));

        // Detect duplicates
        $this->duplicates = $codes->duplicates()->toArray();

        $user = Auth::user();

        $query = Order::with(['user', 'area'])
            ->whereIn('waybill_number', $codes->unique());

        // ✅ Only admins can see all, others only their orders
        if (method_exists($user, 'isAdmin')) {
            if (! $user->isAdmin()) {
                $query->where('users_id', $user->id);
            }
        } else {
            // fallback: allow only the user's own orders if no isAdmin() method
            $query->where('users_id', $user->id);
        }

        $this->orders = $query->get()->map(function ($order) {
            $order->status = ucwords(str_replace('_', ' ', $order->status));

            return $order;
        })->toArray();
    }

    /** Remove an order by ID */
    public function remove($id)
    {
        $this->orders = collect($this->orders)
            ->reject(fn ($order) => $order['id'] == $id)
            ->values()
            ->toArray();
    }

    protected function getFormSchema(): array
    {
        return [
            Textarea::make('waybills')
                ->label('Waybill Numbers')
                ->placeholder('Scan or paste multiple waybill numbers (one per line or separated by spaces)')
                ->rows(6)
                ->autosize()
                ->required(),
        ];
    }

    public function submitScannedOrders()
    {
        $ids = collect($this->orders)->pluck('id')->toArray();

        return redirect()->route('filament.admin.pages.check-shipments', [
            'order_id' => implode(',', $ids),
        ]);
    }
}
