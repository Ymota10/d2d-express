<?php

namespace App\Filament\Pages;

use App\Models\Order;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class BarcodeScan extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-qr-code';

    protected static string $view = 'filament.pages.barcode-scan';

    protected static ?string $navigationGroup = 'Scanning';

    protected static ?string $navigationLabel = 'Barcode Scan';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->management !== 'track_express';
    }

    public $waybills = '';

    public $orders = [];

    public $duplicates = [];

    public $showStatusModal = false;

    public $newStatus = null;

    /** Search orders */
    public function search(): void
    {
        $codes = collect(preg_split('/[\s,]+/', trim($this->waybills)))
            ->filter()
            ->map(fn ($code) => strtoupper(trim($code)));

        $this->duplicates = $codes->duplicates()->toArray();

        $user = Auth::user();

        $query = Order::with(['user', 'area'])
            ->whereIn('waybill_number', $codes->unique());

        if (method_exists($user, 'isAdmin')) {
            if (! $user->isAdmin()) {
                $query->where('users_id', $user->id);
            }
        } else {
            $query->where('users_id', $user->id);
        }

        $this->orders = $query->get()->map(function ($order) {
            $order->status = ucwords(str_replace('_', ' ', $order->status));

            return $order;
        })->toArray();
    }

    /** Remove order */
    public function remove($id)
    {
        $this->orders = collect($this->orders)
            ->reject(fn ($order) => $order['id'] == $id)
            ->values()
            ->toArray();
    }

    /** Open modal */
    public function openStatusModal()
    {
        $this->showStatusModal = true;
    }

    /** Update status */
    public function updateStatus()
    {
        if (auth()->user()?->management !== 'admin') {
            abort(403);
        }

        $ids = collect($this->orders)->pluck('id');

        // ✅ WARNING if empty
        if ($ids->isEmpty()) {
            Notification::make()
                ->title('No Orders Selected')
                ->body('Please scan or select at least one order.')
                ->warning()
                ->send();

            return;
        }

        Order::whereIn('id', $ids)->update([
            'status' => $this->newStatus,
        ]);

        $this->showStatusModal = false;
        $this->newStatus = null;

        $this->search();

        // ✅ SUCCESS MESSAGE (enhanced)
        Notification::make()
            ->title('Status Updated Successfully')
            ->body(count($ids).' orders updated to '.str_replace('_', ' ', $this->newStatus))
            ->success()
            ->send();
    }

    protected function getFormSchema(): array
    {
        return [
            Textarea::make('waybills')
                ->label('Waybill Numbers')
                ->placeholder('Scan or paste multiple waybill numbers (one per line or separated by spaces)')
                ->rows(6)
                ->autosize()
                ->autofocus()
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
