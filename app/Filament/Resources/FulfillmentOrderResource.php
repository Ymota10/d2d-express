<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FulfillmentOrderResource\Pages;
use App\Models\FulfillmentOrder;
use App\Models\WarehouseItem;
use App\Services\WarehouseStockService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class FulfillmentOrderResource extends Resource
{
    protected static ?string $model = FulfillmentOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Warehousing';

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return $user && ($user->warehousing || $user->management === 'admin');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->label('Shipper')
                ->relationship('user', 'name')
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\Select::make('warehouse_subscription_id')
                ->label('Warehouse Subscription')
                ->options(
                    \App\Models\WarehouseSubscription::query()
                        ->with('plan')
                        ->get()
                        ->mapWithKeys(fn ($sub) => [
                            $sub->id => $sub->plan->name.' ('.ucfirst($sub->status).')',
                        ])
                )
                ->searchable()
                ->required(),

            Forms\Components\TextInput::make('reference')
                ->required()
                ->maxLength(50),

            Forms\Components\Select::make('status')
                ->options([
                    'pending' => 'Pending',
                    'packed' => 'Packed',
                    'shipped' => 'Shipped',
                    'delivered' => 'Delivered',
                ])
                ->default('pending')
                ->required(),

            Forms\Components\Repeater::make('orderItems')
                ->relationship()
                ->label('Order Items')
                ->schema([
                    Forms\Components\Select::make('warehouse_item_id')
                        ->label('Item')
                        ->options(WarehouseItem::query()->pluck('name', 'id'))
                        ->searchable()
                        ->required(),

                    Forms\Components\TextInput::make('quantity')
                        ->numeric()
                        ->minValue(1)
                        ->required(),
                ])
                ->minItems(1)
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')->searchable(),
                Tables\Columns\TextColumn::make('user.name')->label('Shipper'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'pending' => 'warning',
                        'packed' => 'primary',
                        'shipped' => 'info',
                        'delivered' => 'success',
                    ]),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->actions([
                // Pack button
                Action::make('pack')
                    ->label('Pack Order')
                    ->color('primary')
                    ->icon('heroicon-o-archive-box')
                    ->requiresConfirmation()
                    ->visible(fn (FulfillmentOrder $record) => $record->status === 'pending')
                    ->action(function (FulfillmentOrder $record) {
                        DB::transaction(function () use ($record) {
                            $stockService = app(WarehouseStockService::class);
                            foreach ($record->orderItems as $item) {
                                $stockService->out(
                                    $item->warehouse_item_id,
                                    $item->quantity,
                                    $record->id
                                );
                            }
                            $record->update(['status' => 'packed']);
                        });
                    }),

                // Ship button
                Action::make('ship')
                    ->label('Ship Order')
                    ->color('info')
                    ->icon('heroicon-o-truck')
                    ->requiresConfirmation()
                    ->visible(fn (FulfillmentOrder $record) => $record->status === 'packed')
                    ->action(fn (FulfillmentOrder $record) => $record->update(['status' => 'shipped'])),

                // Deliver button
                Action::make('deliver')
                    ->label('Mark Delivered')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->visible(fn (FulfillmentOrder $record) => $record->status === 'shipped')
                    ->action(fn (FulfillmentOrder $record) => $record->update(['status' => 'delivered'])),

                // Default Filament actions
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFulfillmentOrder::route('/'),
            'create' => Pages\CreateFulfillmentOrder::route('/create'),
            'edit' => Pages\EditFulfillmentOrder::route('/{record}/edit'),
        ];
    }
}
