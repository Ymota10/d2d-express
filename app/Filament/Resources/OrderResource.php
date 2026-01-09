<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Area;
use App\Models\AreaTier1;
use App\Models\AreaTier2;
use App\Models\Order;
use App\Models\User;
use ArPHP\I18N\Arabic;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (Auth::user()->management === 'shipper') {
            return $query->where('users_id', Auth::id());
        }

        return $query;
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                // Shipper select
                Forms\Components\Select::make('users_id')
                    ->label('Shipper')
                    ->options(User::where('management', 'shipper')->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->default(fn () => Auth::user()->management === 'shipper' ? auth()->id() : null)
                    ->disabled(fn () => Auth::user()->management !== 'admin')
                    ->dehydrated(true),

                // Area select
                Forms\Components\Select::make('area_id')
                    ->label('Area')
                    ->searchable()
                    ->preload()
                    ->options(function () {
                        $branchId = auth()->user()->branch_id ?? null;

                        if ($branchId == 2) {
                            return AreaTier1::pluck('name', 'id');
                        } elseif ($branchId == 4) {
                            return AreaTier2::pluck('name', 'id');
                        } else {
                            return Area::pluck('name', 'id');
                        }
                    })
                    ->getOptionLabelUsing(function ($value) {
                        $branchId = auth()->user()->branch_id ?? null;

                        if ($branchId == 2) {
                            $area = AreaTier1::find($value);
                        } elseif ($branchId == 4) {
                            $area = AreaTier2::find($value);
                        } else {
                            $area = Area::find($value);
                        }

                        return $area ? $area->name.' - '.$area->name_ar : null;
                    })
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if (! $state) {
                            return;
                        }

                        $userId = $get('users_id');
                        $branchId = User::find($userId)?->branch_id ?? null;

                        if ($branchId == 2) {
                            $area = AreaTier1::find($state);
                        } elseif ($branchId == 4) {
                            $area = AreaTier2::find($state);
                        } else {
                            $area = Area::find($state);
                        }

                        if (! $area) {
                            return;
                        }

                        if ($get('service_type') === 'replacement') {
                            $set('delivery_cost', $area->exchange_cost);
                        } else {
                            $set('delivery_cost', $area->delivery_cost);
                        }

                        $set('city_id', $area->city_id);
                    }),

                Forms\Components\Hidden::make('city_id'),

                // Receiver Info section
                Forms\Components\Section::make('Receiver Info')->schema([
                    Forms\Components\TextInput::make('receiver_mobile_1')
                        ->label('Receiver Mobile 1')
                        ->numeric()
                        ->required()
                        ->rule('regex:/^(010|011|012|015)[0-9]{8}$/')
                        ->maxLength(11)
                        ->validationAttribute('Receiver Mobile 1'),

                    Forms\Components\TextInput::make('receiver_mobile_2')
                        ->label('Receiver Mobile 2')
                        ->numeric()
                        ->rule('regex:/^(010|011|012|015)[0-9]{8}$/')
                        ->maxLength(11)
                        ->validationAttribute('Receiver Mobile 2'),

                    Forms\Components\TextInput::make('receiver_name')
                        ->maxLength(255)
                        ->required(),

                    Forms\Components\TextInput::make('client_id'),

                    Forms\Components\Textarea::make('receiver_address')
                        ->required(),

                    Forms\Components\TextInput::make('delivery_cost')
                        ->numeric()
                        ->default(0.00)
                        ->disabled()
                        ->dehydrated(true),
                ])->columns(2),

                // Shipment Data section
                Forms\Components\Section::make('Shipment Data')->schema([
                    Forms\Components\TextInput::make('item_name')->maxLength(255),
                    Forms\Components\Textarea::make('description'),
                    Forms\Components\Textarea::make('notes'),
                    Forms\Components\TextInput::make('order_id')->maxLength(255),
                    Forms\Components\TextInput::make('flyer_no')->maxLength(255),
                    Forms\Components\TextInput::make('cod_amount')
                        ->numeric()
                        ->placeholder('Including Shipping Fees')
                        ->required(),

                    Forms\Components\Select::make('service_type')
                        ->options([
                            'normal_cod' => 'Normal COD',
                            'replacement' => 'Replacement',
                            'refund' => 'Refund',
                        ])
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            $areaId = $get('area_id');
                            $userId = $get('users_id');

                            if (! $areaId || ! $userId) {
                                return;
                            }

                            $branchId = User::find($userId)?->branch_id ?? null;

                            if ($branchId == 2) {
                                $area = AreaTier1::find($areaId);
                            } elseif ($branchId == 4) {
                                $area = AreaTier2::find($areaId);
                            } else {
                                $area = Area::find($areaId);
                            }

                            if (! $area) {
                                return;
                            }

                            $set('delivery_cost', $state === 'replacement' ? $area->exchange_cost : $area->delivery_cost);
                        }),

                    Forms\Components\TextInput::make('weight')->numeric()->placeholder('in kilograms'),
                    Forms\Components\TextInput::make('size')->maxLength(255),
                    Forms\Components\TextInput::make('quantity')->numeric()->default(1)->required(),
                ])->columns(3),

                // Order Status section
                Forms\Components\Section::make('Order Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pickup_request' => 'Pickup Request',
                                'warehouse_received' => 'Warehouse Received',
                                'out_for_delivery' => 'Out for Delivery',
                                'success_delivery' => 'Successful Delivery',
                                'partial_return' => 'Partial Delivery',
                                'partial_return_2' => 'Partial Return',
                                'time_scheduled' => 'Time Scheduled',
                                'undelivered' => 'Undelivered',
                                'returned_and_cost_paid' => 'Returned and cost paid',
                                'returned_to_warehouse' => 'Returned to Warehouse',
                                'returned_to_shipper' => 'Returned to Shipper',
                            ])
                            ->required()
                            ->reactive()
                            ->default('pickup_request')
                            ->visible(fn () => auth()->user()?->management === 'admin'),

                        Forms\Components\Select::make('open_package')
                            ->label('Open Package')
                            ->options(['yes' => 'Yes', 'no' => 'No'])
                            ->default('no')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('open_package_fee', $state === 'yes' ? 5 : 0)),

                        Forms\Components\TextInput::make('open_package_fee')
                            ->label('Open Package Fee')
                            ->numeric()
                            ->default(0.00)
                            ->disabled()
                            ->dehydrated(true),

                        Forms\Components\Select::make('undelivered_reason')
                            ->options([
                                'refused_payment' => 'Refused Payment',
                                'no_answer' => 'No Answer',
                                'wrong_location' => 'Wrong Location',
                                'refused_shipment' => 'Refused Shipment',
                                'consignee_escaped' => 'Consignee Escaped',
                            ])
                            ->required()
                            ->visible(fn ($get) => $get('status') === 'undelivered'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('waybill_number')
                    ->label('Waybill No.')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')->label('Shipper')->sortable(),
                // ->visible(fn () => Auth::user()->management === 'admin'), // ✅ Only admin can see

                Tables\Columns\TextColumn::make('area.name')->label('Area')->sortable(),
                Tables\Columns\TextColumn::make('receiver_address')->sortable(),
                Tables\Columns\TextColumn::make('receiver_name')->searchable(),
                Tables\Columns\TextColumn::make('receiver_mobile_1'),
                Tables\Columns\TextColumn::make('item_name'),
                Tables\Columns\TextColumn::make('cod_amount')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('service_type')
                    ->label('Service Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'normal_cod' => 'Normal COD',
                        'replacement' => 'Replacement',
                        'refund' => 'Refund',
                        // 'same_day_delivery' => 'Same Day Delivery',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(function (string $state): string {
                        return match ($state) {
                            'pickup_request' => 'Pickup Request',
                            'warehouse_received' => 'Warehouse Received',
                            'out_for_delivery' => 'Out for Delivery',
                            'success_delivery' => 'Success Delivery',
                            'partial_return' => 'Partial Delivery',
                            'partial_return_2' => 'Partial Return',
                            'time_scheduled' => 'Time Scheduled',
                            'undelivered' => 'Undelivered',
                            'returned_and_cost_paid' => 'Returned and cost paid',
                            'returned_to_warehouse' => 'Returned to Warehouse',
                            'returned_to_shipper' => 'Returned to Shipper',
                            default => ucfirst(str_replace('_', ' ', $state)),
                        };
                    })
                    ->color(function (string $state): string {
                        return match ($state) {
                            'pickup_request' => 'warning',   // orange
                            'warehouse_received' => 'six',  // blue
                            'out_for_delivery' => 'third', // light green
                            'success_delivery' => 'success', // green
                            'partial_return' => 'orange', // green
                            'partial_return_2' => 'partialreturn',
                            'time_scheduled' => 'secondary',    // purple (requires custom theme if not default)
                            'undelivered' => 'danger',       // red
                            'returned_and_cost_paid' => 'seven',
                            'returned_to_warehouse' => 'fifth', //
                            'returned_to_shipper' => 'fourth', // Black
                            default => 'secondary',
                        };
                    }),

                Tables\Columns\BadgeColumn::make('attempts_visual')
                    ->label('Attempts')
                    ->getStateUsing(function ($record) {
                        return $record->attempts()->count();
                    })
                    ->formatStateUsing(function ($state) {
                        return $state.' / 3';
                    })
                    ->colors([
                        'success' => fn ($state) => $state === 1,   // ✅ 1/3 = green
                        'warning' => fn ($state) => $state === 2,   // ✅ 2/3 = yellow
                        'danger' => fn ($state) => $state >= 3,    // ✅ 3+/3 = red
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => fn ($state) => $state === 1,
                        'heroicon-o-arrow-path' => fn ($state) => $state === 2,
                        'heroicon-o-exclamation-triangle' => fn ($state) => $state >= 3,
                    ])
                    ->sortable(
                        query: function ($query, $direction) {
                            $query->withCount('attempts')
                                ->orderBy('attempts_count', $direction);
                        }
                    )
                    ->tooltip(function ($record) {
                        $last = $record->attempts()->latest()->first();

                        return $last
                            ? 'Last attempt: '.ucfirst(str_replace('_', ' ', $last->status))
                            : 'No attempts yet';
                    }),

                Tables\Columns\TextColumn::make('undelivered_reason')
                    ->label('Undelivered Reason')
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state)))
                    ->visible(fn ($record) => optional($record)->status === 'undelivered'),

                Tables\Columns\BadgeColumn::make('open_package')
                    ->label('Open Package')
                    ->colors([
                        'success' => 'yes',
                        'danger' => 'no',
                    ]),

                Tables\Columns\TextColumn::make('order_id')->sortable(),
                Tables\Columns\TextColumn::make('delivery_cost')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('open_package_fee')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M d, Y h:i A') // 12-hour format with AM/PM
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('M d, Y h:i A') // 12-hour format with AM/PM
                    ->sortable(),

            ])
            ->filters([

                // 📅 Date Range Filter
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From Date'),
                        Forms\Components\DatePicker::make('to')->label('To Date'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            $data['from'] ?? null,
                            fn ($q, $date) => $q->whereDate('updated_at', '>=', $date)
                        )
                        ->when(
                            $data['to'] ?? null,
                            fn ($q, $date) => $q->whereDate('updated_at', '<=', $date)
                        )
                    ),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pickup_request' => 'Pickup Request',
                        'warehouse_received' => 'Warehouse Received',
                        'out_for_delivery' => 'Out for Delivery',
                        'success_delivery' => 'Successful Delivery',
                        'partial_return' => 'Partial Delivery',
                        'partial_return_2' => 'Partial Return',
                        'time_scheduled' => 'Time Scheduled',
                        'undelivered' => 'Undelivered',
                        'returned_and_cost_paid' => 'Returned and cost paid',
                        'returned_to_warehouse' => 'Returned to Warehouse',
                        'returned_to_shipper' => 'Returned to Shipper',
                    ])
                    ->searchable()
                    ->multiple(),

                Tables\Filters\SelectFilter::make('service_type')
                    ->label('Service Type')
                    ->options([
                        'normal_cod' => 'Normal COD',
                        'replacement' => 'Replacement',
                        'refund' => 'Refund',
                    ])
                    ->searchable()
                    ->multiple(),

                // 🔍 Filter by Receiver Name
                Tables\Filters\Filter::make('receiver_name')
                    ->form([
                        Forms\Components\TextInput::make('receiver_name')
                            ->label('Receiver Name')
                            ->placeholder('Enter receiver name'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when($data['receiver_name'], fn ($q, $value) => $q->where('receiver_name', 'like', "%{$value}%")
                    )
                    ),

                // 🔍 Filter by Receiver Mobile
                Tables\Filters\Filter::make('receiver_mobile')
                    ->form([
                        Forms\Components\TextInput::make('receiver_mobile')
                            ->label('Receiver Mobile')
                            ->placeholder('Enter receiver mobile number'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when($data['receiver_mobile'], fn ($q, $value) => $q->where(function ($q) use ($value) {
                        $q->where('receiver_mobile_1', 'like', "%{$value}%")
                            ->orWhere('receiver_mobile_2', 'like', "%{$value}%");
                    })
                    )
                    ),

                // 🔍 Filter by Shipper
                Tables\Filters\SelectFilter::make('users_id')
                    ->label('Shipper')
                    ->options(User::where('management', 'shipper')->pluck('name', 'id'))
                    ->searchable()
                    ->visible(fn () => auth()->user()?->management === 'admin'),

                // 🔍 Filter by City
                Tables\Filters\SelectFilter::make('city_id')
                    ->label('City')
                    ->relationship('city', 'name') // ✅ Assumes you have city() relation in Order model
                    ->searchable(),

                // 🔍 Filter by Waybill Number
                Tables\Filters\Filter::make('waybill_number')
                    ->form([
                        Forms\Components\TextInput::make('waybill_number')
                            ->label('Waybill Number')
                            ->placeholder('Enter Waybill Number'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when($data['waybill_number'], fn ($q, $value) => $q->where('waybill_number', 'like', "%{$value}%")
                    )
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->color(fn ($record) => auth()->user()->management === 'admin'
                            ? 'primary'
                            : ($record->status === 'pickup_request' ? 'primary' : 'gray')
                    )
                    ->extraAttributes(fn ($record) => [
                        'title' => auth()->user()->management !== 'admin' && $record->status !== 'pickup_request'
                            ? 'You cannot edit order in this current state'
                            : 'Edit this order',
                        'style' => auth()->user()->management !== 'admin' && $record->status !== 'pickup_request'
                            ? 'cursor:not-allowed; opacity:0.6; pointer-events:auto;'
                            : '',
                    ])
                    ->action(function ($record, $livewire) {
                        $user = auth()->user();

                        // ✅ Admins can always edit
                        if ($user->management === 'admin') {
                            return redirect(static::getUrl('edit', ['record' => $record]));
                        }

                        // ✅ Non-admins restricted unless pickup_request
                        if ($record->status !== 'pickup_request') {
                            \Filament\Notifications\Notification::make()
                                ->title('You cannot edit order in this current state')
                                ->warning()
                                ->send();

                            return;
                        }

                        // ✅ Normal edit behavior
                        return redirect(static::getUrl('edit', ['record' => $record]));
                    }),

                Tables\Actions\DeleteAction::make(),

                Tables\Actions\Action::make('track')
                    ->label('Order Tracking')
                    ->icon('heroicon-o-map-pin')
                    ->color('info')
                    ->url(fn (Order $record): string => static::getUrl('track-order', ['record' => $record]))
                    ->openUrlInNewTab(),
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make(array_filter([
                    // ✅ Only show if user is admin
                    auth()->user()?->isAdmin()
                        ? Tables\Actions\BulkAction::make('change_status')
                            ->label('Change Status')
                            ->icon('heroicon-o-strikethrough')
                            ->color('tertiary')
                            ->form([
                                Forms\Components\Select::make('status')
                                    ->label('Select New Status')
                                    ->options([
                                        'pickup_request' => 'Pickup Request',
                                        'warehouse_received' => 'Warehouse Received',
                                        'out_for_delivery' => 'Out for Delivery',
                                        'success_delivery' => 'Successful Delivery',
                                        'partial_return' => 'Partial Delivery',
                                        'partial_return_2' => 'Partial Return',
                                        'time_scheduled' => 'Time Scheduled',
                                        'undelivered' => 'Undelivered',
                                        'returned_and_cost_paid' => 'Returned and cost paid',
                                        'returned_to_warehouse' => 'Returned to Warehouse',
                                        'returned_to_shipper' => 'Returned to Shipper',
                                    ])
                                    ->required(),
                            ])
                            ->action(function (array $data, \Illuminate\Support\Collection $records) {
                                foreach ($records as $order) {
                                    $order->update([
                                        'status' => $data['status'],
                                    ]);
                                }

                                \Filament\Notifications\Notification::make()
                                    ->title('Orders updated successfully')
                                    ->success()
                                    ->send();
                            })
                            ->requiresConfirmation()
                        : null,

                    Tables\Actions\DeleteBulkAction::make(),

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

                    Tables\Actions\BulkAction::make('import_orders')
                        ->label('Import Orders')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('warning')
                        ->form([
                            \Filament\Forms\Components\FileUpload::make('file')
                                ->label('Select Excel File')
                                ->required()
                                ->disk('public')
                                ->directory('imports')
                                ->rules(['mimes:xlsx,xls']),
                        ])
                        ->action(function (array $data) {
                            $filePath = storage_path('app/public/'.$data['file']);

                            if (! file_exists($filePath)) {
                                throw new \Exception('File not found: '.$filePath);
                            }

                            $import = new \App\Imports\OrdersImport;
                            \Maatwebsite\Excel\Facades\Excel::import($import, $filePath);

                            $summary = $import->getSummary();

                            \Filament\Notifications\Notification::make()
                                ->title('📦 Orders Import Summary')
                                ->body("
                    ✅ {$summary['success']} orders imported successfully.  
                    ⚠️ {$summary['skipped']} skipped.  
                    ❌ {$summary['failures']} failed validation.
                                ")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('download_demo_excel')
                        ->label('Download Demo Excel')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('info')
                        ->requiresConfirmation(false)
                        ->action(function () {
                            return \Maatwebsite\Excel\Facades\Excel::download(
                                new \App\Exports\OrdersDemoExport,
                                'orders_import_demo.xlsx'
                            );
                        }),

                    Tables\Actions\BulkAction::make('print_waybills')
                        ->label('Print Waybills (A4)')
                        ->icon('heroicon-o-printer')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Support\Collection $records) {
                            $Arabic = new Arabic;

                            $orders = $records->map(function ($order) use ($Arabic) {
                                if (! empty($order->address_ar)) {
                                    $order->address_ar = $Arabic->utf8Glyphs($order->address_ar);
                                }
                                if (! empty($order->city_ar)) {
                                    $order->city_ar = $Arabic->utf8Glyphs($order->city_ar);
                                }
                                if (! empty($order->area_ar)) {
                                    $order->area_ar = $Arabic->utf8Glyphs($order->area_ar);
                                }
                                if (! empty($order->receiver_name)) {
                                    $order->receiver_name = $Arabic->utf8Glyphs($order->receiver_name);
                                }

                                return $order;
                            });

                            $pdf = Pdf::loadView('filament.pages.bulk-waybills', [
                                'orders' => $orders,
                                'language' => 'ar',
                            ])
                                ->setOptions([
                                    'isHtml5ParserEnabled' => true,
                                    'isRemoteEnabled' => true,
                                    'defaultFont' => 'Amiri',
                                    'isFontSubsettingEnabled' => true,
                                    'dpi' => 150,
                                ]);

                            $fileName = 'waybills_'.now()->format('Y_m_d_His').'.pdf';

                            return response()->streamDownload(fn () => print ($pdf->output()), $fileName);
                        }),

                    Tables\Actions\BulkAction::make('print_waybills_x')
                        ->label('Print Waybills (X Printer)')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Support\Collection $records) {
                            $Arabic = new Arabic;

                            $orders = $records->map(function ($order) use ($Arabic) {
                                if (! empty($order->address_ar)) {
                                    $order->address_ar = $Arabic->utf8Glyphs($order->address_ar);
                                }
                                if (! empty($order->city_ar)) {
                                    $order->city_ar = $Arabic->utf8Glyphs($order->city_ar);
                                }
                                if (! empty($order->area_ar)) {
                                    $order->area_ar = $Arabic->utf8Glyphs($order->area_ar);
                                }
                                if (! empty($order->receiver_name)) {
                                    $order->receiver_name = $Arabic->utf8Glyphs($order->receiver_name);
                                }

                                return $order;
                            });

                            $pdf = Pdf::loadView('filament.pages.bulk-waybills-x', [
                                'orders' => $orders,
                                'language' => 'ar',
                            ])
                                ->setPaper([0, 0, 226.77, 300]) // 👈 REQUIRED FOR X PRINTER
                                ->setOptions([
                                    'isHtml5ParserEnabled' => true,
                                    'isRemoteEnabled' => true,
                                    'defaultFont' => 'Amiri',
                                    'isFontSubsettingEnabled' => true,
                                    'dpi' => 150,
                                ]);

                            $fileName = 'waybills_x_'.now()->format('Y_m_d_His').'.pdf';

                            return response()->streamDownload(
                                fn () => print ($pdf->output()),
                                $fileName
                            );
                        }),
                ])),
            ]);

    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
            'track-order' => Pages\TrackOrder::route('/{record}/track'),
        ];
    }
}
