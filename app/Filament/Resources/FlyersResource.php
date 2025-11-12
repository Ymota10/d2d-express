<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FlyersResource\Pages;
use App\Models\Flyer;
use App\Models\Order; // make sure you have an Order model/table
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FlyersResource extends Resource
{
    protected static ?string $model = Flyer::class;

    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('size')
                    ->required(),
                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('pack_size')
                    ->numeric()
                    ->default(10),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('')
                    ->getStateUsing(fn ($record) => asset('images/adobe.png')) // fixed path
                    ->size(130),

                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('size'),
                Tables\Columns\TextColumn::make('price')->money('egp'),
                Tables\Columns\TextColumn::make('pack_size'),
            ])
            ->actions([
                // 👇 Custom Order Action
                Tables\Actions\Action::make('order')
                    ->label('Order')
                    ->icon('heroicon-o-shopping-cart')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('customer_name')->required(),

                        Forms\Components\TextInput::make('customer_phone')
                            ->label('Customer Phone')
                            ->required()
                            ->tel()
                            ->mask('99999999999')
                            ->maxLength(11)
                            ->minLength(11)
                            ->rule('regex:/^(010|011|012|015)[0-9]{8}$/')
                            ->validationAttribute('Customer Phone')
                            ->helperText('Phone must start with 010, 011, 012, or 015 and be 11 digits long.'),

                        Forms\Components\Textarea::make('receiver_address')
                            ->label('Receiver Address'),

                        Forms\Components\Select::make('area_id')
                            ->label('Area')
                            ->relationship('area', 'name')
                            ->searchable()
                            ->preload()
                            ->getOptionLabelUsing(fn ($value): ?string => optional(\App\Models\Area::find($value))
                                ?->name.' - '.optional(\App\Models\Area::find($value))?->name_ar
                            )
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $area = \App\Models\Area::find($state);
                                    if ($area) {
                                        $set('delivery_cost', $area->delivery_cost);
                                        $set('city_id', $area->city_id);
                                    }
                                }
                            }),

                        Forms\Components\TextInput::make('quantity')
                            ->numeric()
                            ->minValue(1)
                            ->default(null) // 👈 خليها فاضية في الأول
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get, $record) {
                                if ($record && $state && $get('delivery_cost')) {
                                    $quantity = max(1, $state);
                                    $flyerCost = $quantity * 10 * $record->price;
                                    $delivery = $get('delivery_cost');
                                    $set('total_price', $flyerCost + $delivery);
                                } else {
                                    $set('total_price', null); // 👈 يمسح القيمة لحد ما الشرطين موجودين
                                }
                            }),

                        Forms\Components\TextInput::make('delivery_cost')
                            ->numeric()
                            ->default(null) // 👈 فاضي في الأول
                            ->disabled()
                            ->dehydrated(true)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get, $record) {
                                if ($record && $state && $get('quantity')) {
                                    $quantity = max(1, $get('quantity'));
                                    $flyerCost = $quantity * 10 * $record->price;
                                    $delivery = $state;
                                    $set('total_price', $flyerCost + $delivery);
                                } else {
                                    $set('total_price', null);
                                }
                            }),

                        Forms\Components\TextInput::make('total_price')
                            ->label('Total Price')
                            ->disabled()
                            ->reactive()
                            ->afterStateHydrated(function ($set, $get, $record) {
                                if ($record && $get('quantity') && $get('delivery_cost')) {
                                    $quantity = max(1, $get('quantity'));
                                    $flyerCost = $quantity * 10 * $record->price;
                                    $delivery = $get('delivery_cost');
                                    $set('total_price', $flyerCost + $delivery);
                                } else {
                                    $set('total_price', null);
                                }
                            })
                            ->dehydrated(false),

                    ])
                    ->action(function (Flyer $record, array $data) {
                        $quantity = max(1, $data['quantity']);
                        $flyerCost = $quantity * 10 * $record->price; // price per flyer × 10 × packs
                        $delivery = $data['delivery_cost'] ?? 0;
                        $total = $flyerCost + $delivery;

                        \App\Models\FlyerOrder::create([
                            'flyer_id' => $record->id,
                            'quantity' => $quantity,
                            'total_price' => $total,
                            'customer_name' => $data['customer_name'],
                            'customer_phone' => $data['customer_phone'],
                            'receiver_address' => $data['receiver_address'] ?? null,
                            'area_id' => $data['area_id'] ?? null,
                            'city_id' => $data['city_id'] ?? null,
                            'delivery_cost' => $delivery,
                            'status' => 'pending',
                        ]);
                        // ✅ رسالة نجاح
                        Notification::make()
                            ->title('Order Created Successfully')
                            ->success()
                            ->send();
                    }),

            ]);

        // ->bulkActions([
        //     Tables\Actions\DeleteBulkAction::make(),
        // ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFlyers::route('/'),
            'create' => Pages\CreateFlyers::route('/create'),
            // 'edit' => Pages\EditFlyers::route('/{record}/edit'),
        ];
    }
}
