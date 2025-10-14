<?php

namespace App\Filament\Widgets;

use App\Models\City;
use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class CitySuccessRateWidget extends BaseWidget
{
    protected static ?string $heading = 'Geographical Analysis';

    public function getColumnSpan(): int|string|array
    {
        return 'full'; // ✅ Makes this widget take the full row width
    }

    public function getTableRecordKey($record): string
    {
        return $record->city_id;
    }

    public function table(Table $table): Table
    {
        $user = auth()->user();

        // ✅ Base query for orders
        $query = Order::query()
            ->select([
                'city_id',
                DB::raw('SUM(CASE WHEN status = "success_delivery" THEN 1 ELSE 0 END) as successful_orders'),
                DB::raw('SUM(CASE WHEN status = "undelivered" THEN 1 ELSE 0 END) as unsuccessful_orders'),
            ])
            ->groupBy('city_id')
            ->havingRaw('(successful_orders + unsuccessful_orders) > 0')
            ->orderByDesc('successful_orders');

        // ✅ Restrict to logged-in user's data if not admin
        if (! $user->isAdmin()) {
            $query->where('users_id', $user->id);
        }

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('city.name')
                    ->label('City')
                    ->formatStateUsing(fn ($state, $record) => optional(City::find($record->city_id))?->name ?? 'Unknown City')
                    ->wrap(),

                Tables\Columns\TextColumn::make('closed_orders')
                    ->label('Closed Orders')
                    ->getStateUsing(fn ($record) => $record->successful_orders + $record->unsuccessful_orders)
                    ->alignRight(),

                Tables\Columns\TextColumn::make('success_rate')
                    ->label('Success Rate')
                    ->getStateUsing(function ($record) {
                        $closed = $record->successful_orders + $record->unsuccessful_orders;

                        return $closed > 0
                            ? round(($record->successful_orders / $closed) * 100, 1)
                            : 0;
                    })
                    ->formatStateUsing(function ($state) {
                        // ✅ Choose color dynamically based on percentage
                        if ($state < 50) {
                            $color = '#dc2626'; // red
                        } elseif ($state < 80) {
                            $color = '#facc15'; // yellow
                        } else {
                            $color = '#028a0f'; // green
                        }

                        return new HtmlString('
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="h-3 rounded-full transition-all duration-700" 
                                    style="width: '.$state.'%; background-color: '.$color.';">
                                </div>
                            </div>
                            <span class="text-xs ml-1">'.$state.'%</span>
                        ');
                    })
                    ->html(),
            ]);
    }
}
