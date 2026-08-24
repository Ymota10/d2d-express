<?php

namespace App\Filament\Resources\PaymentReportResource\Widgets;

use App\Models\Order;
use App\Models\PaymentReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Maatwebsite\Excel\Facades\Excel;

class OrdersInReportTable extends BaseWidget
{
    public ?PaymentReport $record = null;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $orderIds = json_decode($this->record?->order_ids ?? '[]', true);

        return $table
            ->heading('Select orders to export excel sheet')
            ->query(Order::query()->whereIn('id', $orderIds))
            ->columns([
                Tables\Columns\TextColumn::make('waybill_number')->label('Waybill')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('service_type')
                    ->label('Service Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'normal_cod' => 'Normal COD',
                        'replacement' => 'Exchange',
                        'refund' => 'Refund',
                        'same_day_delivery' => 'Same Day Delivery',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    }),
                // Tables\Columns\TextColumn::make('order_id')->label('Order ID')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('Shipper')->searchable()->sortable()
                    ->visible(fn () => auth()->user()?->management === 'admin'),
                Tables\Columns\TextColumn::make('receiver_name')->label('Receiver Name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('receiver_mobile_1')->label('Receiver Mobile 1')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('area.name')->label('Area')->searchable()->sortable(),
                // Tables\Columns\TextColumn::make('city.name')->label('City')->searchable()->sortable(),

                // Tables\Columns\TextColumn::make('receiver_mobile_2')->label('Receiver Mobile 2')->searchable()->sortable(),
                // Tables\Columns\TextColumn::make('receiver_address')
                //     ->label('Address')
                //     ->limit(50)
                //     ->tooltip(fn ($record) => $record->receiver_address)
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('item_name')->label('Item')->searchable()->sortable(),
                // Tables\Columns\TextColumn::make('quantity')->label('Qty')->numeric()->sortable(),
                // Tables\Columns\TextColumn::make('size')->label('Size')->sortable(),
                // Tables\Columns\TextColumn::make('weight')->label('Weight')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('cod_amount')->label('COD Amount')->money('EGP')->sortable(),

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

                Tables\Columns\TextColumn::make('delivery_cost')->label('Delivery Cost')->searchable()->sortable(),

                Tables\Columns\BadgeColumn::make('open_package')
                    ->label('Open Package')
                    ->colors([
                        'success' => 'yes',
                        'danger' => 'no',
                    ]),

                Tables\Columns\TextColumn::make('open_package_fee')
                    ->label('Open Package Fees')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('insurancePackage.name')
                    ->label('Insurance')
                    ->placeholder('No Insurance')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('insurance_fee')
                    ->label('Insurance Fees')
                    ->money('EGP')
                    ->sortable(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([

                    /*
                     * =====================================================
                     * EXPORT EXCEL
                     * =====================================================
                     */
                    Tables\Actions\BulkAction::make('export_selected')
                        ->label('Export Selected')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {

                            $ids = $records
                                ->pluck('id')
                                ->toArray();

                            return Excel::download(
                                new \App\Exports\OrdersExport($ids),
                                'lynk_payment_report_orders.xlsx'
                            );
                        }),

                    /*
                     * =====================================================
                     * EXPORT PDF INVOICE
                     * =====================================================
                     */
                    Tables\Actions\BulkAction::make('export_pdf_invoice')
                        ->label('Export Invoice (PDF)')
                        ->icon('heroicon-o-document-text')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records) {

                            $orders = Order::with([
                                'user',
                                'area',
                                'city',
                            ])
                                ->whereIn('id', $records->pluck('id'))
                                ->get();

                            if ($orders->isEmpty()) {
                                return;
                            }

                            $pdf = Pdf::loadView(
                                'pdf.payment-report-invoice',
                                [
                                    'orders' => $orders,
                                    'paymentReport' => $this->record,
                                ]
                            );

                            $pdf->setPaper('A4', 'portrait');

                            return response()->streamDownload(
                                function () use ($pdf) {
                                    echo $pdf->output();
                                },
                                'lynk_payment_report_invoice.pdf'
                            );
                        }),
                ]),
            ]);
    }
}
