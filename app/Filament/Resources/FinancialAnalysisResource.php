<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FinancialAnalysisResource\Pages;
use App\Models\Order;
use App\Models\PaymentReport;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class FinancialAnalysisResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $pluralLabel = 'Financial Analysis';

    protected static ?string $modelLabel = 'Financial Analysis';

    protected static ?string $navigationGroup = 'Finance';

    protected static ?string $navigationLabel = 'Financial Analysis';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form;
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        $user = Auth::user();

        // Base query: success_delivery + undelivered (both visible)
        $query = Order::query()
            ->whereIn('status', ['success_delivery', 'undelivered', 'returned_to_shipper', 'partial_return']);
        // Restrict to shipper’s own orders if not admin
        if ($user->isShipper()) {
            $query->where('users_id', $user->id);
        }

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('waybill_number')->label('Waybill')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('order_id')->label('Order ID')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('Shipper')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('area.name')->label('Area')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('city.name')->label('City')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('receiver_name')->label('Receiver')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('receiver_mobile_1')->label('Receiver Mobile 1')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('receiver_mobile_2')->label('Receiver Mobile 2')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('receiver_address')
                    ->label('Address')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->receiver_address)
                    ->sortable(),
                Tables\Columns\TextColumn::make('item_name')->label('Item')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('quantity')->label('Qty')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('size')->label('Size')->sortable(),
                Tables\Columns\TextColumn::make('weight')->label('Weight')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('cod_amount')->label('COD Amount')->money('EGP')->sortable(),
                Tables\Columns\TextColumn::make('delivery_cost')->label('Delivery Cost')->sortable()->searchable(),

                Tables\Columns\TextColumn::make('service_type')
                    ->label('Service Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'normal_cod' => 'Normal COD',
                        'replacement' => 'Replacement',
                        'refund' => 'Refund',
                        'same_day_delivery' => 'Same Day Delivery',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pickup_request' => 'Pickup Request',
                        'warehouse_received' => 'Warehouse Received',
                        'out_for_delivery' => 'Out for Delivery',
                        'success_delivery' => 'Success Delivery',
                        'partial_return' => 'Partial Delivery',
                        'time_scheduled' => 'Time Scheduled',
                        'undelivered' => 'Undelivered',
                        'returned_to_warehouse' => 'Returned to Warehouse',
                        'returned_to_shipper' => 'Returned to Shipper',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->color(fn (string $state) => match ($state) {
                        'pickup_request' => 'warning',
                        'warehouse_received' => 'six',
                        'out_for_delivery' => 'third',
                        'success_delivery' => 'success',
                        'partial_return' => 'orange',
                        'time_scheduled' => 'secondary',
                        'undelivered' => 'danger',
                        'returned_to_warehouse' => 'fifth',
                        'returned_to_shipper' => 'fourth',
                        default => 'secondary',
                    }),

                Tables\Columns\BadgeColumn::make('open_package')
                    ->label('Open Package')
                    ->colors([
                        'success' => 'yes',
                        'danger' => 'no',
                    ]),

                Tables\Columns\TextColumn::make('open_package_fee')->numeric()->sortable(),

                Tables\Columns\IconColumn::make('is_collected')
                    ->label('Collected')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('created_at')->label('Created At')->dateTime()->sortable(),
            ])
            ->filters([

                // 📅 Date Range Filter
                Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From Date'),
                        Forms\Components\DatePicker::make('to')->label('To Date'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'], fn ($q, $date) => $q->whereDate('updated_at', '>=', $date))
                        ->when($data['to'], fn ($q, $date) => $q->whereDate('updated_at', '<=', $date))
                    ),

                // 🔍 Keyword Filter
                Filter::make('keyword')
                    ->form([
                        Forms\Components\TextInput::make('keyword')
                            ->label('Keyword')
                            ->placeholder('Enter receiver or item name...'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['keyword'], fn ($q, $keyword) => $q->where(fn ($subQuery) => $subQuery
                            ->where('receiver_name', 'like', "%{$keyword}%")
                            ->orWhere('item_name', 'like', "%{$keyword}%")
                        ))
                    ),

                // 🧍‍♂️ Shipper Filter (Admins only)
                Tables\Filters\SelectFilter::make('users_id')
                    ->label('Shipper')
                    ->options(
                        \App\Models\User::where('management', 'shipper')->pluck('name', 'id')
                    )
                    ->searchable()
                    ->visible(fn () => auth()->user()?->management === 'admin'),

                // 💰 Collected Status Filter
                Tables\Filters\SelectFilter::make('is_collected')
                    ->label('Collected Status')
                    ->options([
                        1 => 'Collected',
                        0 => 'Not Collected',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] !== null) {
                            $query->where('is_collected', $data['value']);
                        }
                    }),

            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([

                    // ✅ Admins only: Collection
                    Tables\Actions\BulkAction::make('collection')
                        ->label('Collection')
                        ->icon('heroicon-o-archive-box-arrow-down')
                        ->color('success')
                        ->visible(fn () => auth()->user()?->isAdmin()) // 👈 show only to admins
                        ->requiresConfirmation()
                        ->form(function ($records) {
                            $records = $records->where('is_collected', false);

                            $totalCod = $records
                                ->whereIn('status', ['success_delivery', 'partial_return'])
                                ->sum('cod_amount');
                            $totalShipping = $records->sum('delivery_cost');
                            $count = $records->count();

                            return [
                                Forms\Components\Placeholder::make('orders_count')
                                    ->label('Total Orders')
                                    ->content($count),

                                Forms\Components\Placeholder::make('total_cod')
                                    ->label('Total COD Collected')
                                    ->content(number_format($totalCod, 2).' EGP'),

                                Forms\Components\Placeholder::make('total_shipping')
                                    ->label('Total Shipping Fees')
                                    ->content(number_format($totalShipping, 2).' EGP'),

                                Forms\Components\Radio::make('extra_type')
                                    ->label('Extra Fees Type')
                                    ->options([
                                        'fixed' => 'Fixed Amount',
                                        'percent' => 'Percentage of COD',
                                        'none' => 'No Extra Fees',
                                    ])
                                    ->default('none'),

                                Forms\Components\TextInput::make('extra_value')
                                    ->label('Extra Value')
                                    ->numeric()
                                    ->default(0),
                            ];
                        })
                        ->action(function ($records, array $data) {
                            $records = $records->where('is_collected', false);

                            $totalCod = $records
                                ->whereIn('status', ['success_delivery', 'partial_return'])
                                ->sum('cod_amount');
                            $totalShipping = $records->sum('delivery_cost');
                            $openPackageFees = $records->sum('open_package_fee'); // ✅ deduct open package fee
                            $extra = 0;

                            if ($data['extra_type'] === 'fixed') {
                                $extra = (float) $data['extra_value'];
                            } elseif ($data['extra_type'] === 'percent') {
                                $extra = $totalCod * ((float) $data['extra_value'] / 100);
                            }

                            // ✅ Deduct open package fees from net profit
                            $finalAmount = $totalCod - $totalShipping - $extra - $openPackageFees;

                            // ✅ Get shipper ID (from the first order in the group)
                            $shipperId = $records->first()->shipper_id ?? null;

                            // ✅ Create Payment Report
                            $report = PaymentReport::create([
                                'user_id' => auth()->id(), // who created the report
                                'shipper_id' => $shipperId, // link report to shipper
                                'order_ids' => json_encode($records->pluck('id')->toArray()),
                                'total_cod' => $totalCod,
                                'total_delivery_cost' => $totalShipping,
                                'extra_fees' => $extra,
                                'final_amount' => $finalAmount,
                            ]);

                            // ✅ Mark collected orders
                            foreach ($records as $order) {
                                $order->update(['is_collected' => true]);
                            }

                            return redirect(\App\Filament\Resources\PaymentReportResource::getUrl('index'));
                        }),

                    // ✅ Everyone can export
                    Tables\Actions\BulkAction::make('export_selected')
                        ->label('Export Selected')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $ids = $records->pluck('id')->toArray();

                            return \Maatwebsite\Excel\Facades\Excel::download(
                                new \App\Exports\OrdersExport($ids),
                                'D2D_financial_analysis.xlsx'
                            );
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFinancialAnalyses::route('/'),
        ];
    }
}
