<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class UnsuccessfulReasonsWidget extends BaseWidget
{
    protected static ?string $heading = 'Unsuccessful Delivery Reasons';

    public function getTableRecordKey($record): string
    {
        return $record->reason;
    }

    public function table(Table $table): Table
    {
        $user = auth()->user();

        // ✅ Start query
        $ordersQuery = Order::query()
            ->where('status', 'undelivered');

        // ✅ Non-admin users only see their own orders
        if (! $user->isAdmin()) {
            $ordersQuery->where('users_id', $user->id);
        }

        // ✅ Base grouped query
        $query = $ordersQuery
            ->select([
                DB::raw('COALESCE(undelivered_reason, "Unknown") as reason'),
                DB::raw('COUNT(*) as count'),
            ])
            ->groupBy('reason')
            ->orderByDesc('count');

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('reason')
                    ->label('Reason')
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state)))
                    ->wrap(),

                Tables\Columns\TextColumn::make('count')
                    ->label('Count')
                    ->alignRight(),

                Tables\Columns\TextColumn::make('percentage')
                    ->label('Percentage')
                    ->getStateUsing(function ($record) use ($user) {
                        // ✅ Reapply visibility logic for percentage
                        $totalQuery = Order::where('status', 'undelivered');

                        if (! $user->isAdmin()) {
                            $totalQuery->where('users_id', $user->id);
                        }

                        $total = $totalQuery->count();

                        return $total > 0
                            ? round(($record->count / $total) * 100, 1)
                            : 0;
                    })
                    ->formatStateUsing(function ($state) {
                        return new HtmlString('
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="h-3 rounded-full" 
                                    style="width: '.$state.'%; background-color: #02447d;"></div>
                            </div>
                            <span class="text-xs ml-1">'.$state.'%</span>
                        ');
                    })
                    ->html(),
            ]);
    }
}
