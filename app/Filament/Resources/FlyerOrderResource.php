<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FlyerOrderResource\Pages;
use App\Models\Area;
use App\Models\City;
use App\Models\Flyer;
use App\Models\FlyerOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FlyerOrderResource extends Resource
{
    protected static ?string $model = FlyerOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    // ✅ Only show in sidebar for admins
    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return $user && $user->isAdmin();
    }

    // ✅ Only allow access for admins
    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && $user->isAdmin();
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('flyer_id')
                    ->label('Flyer')
                    ->options(Flyer::pluck('name', 'id'))
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($flyer = Flyer::find($state)) {
                            $set('total_price', $flyer->price);
                        }
                    }),

                Forms\Components\TextInput::make('quantity')
                    ->numeric()
                    ->default(1)
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if ($flyer = Flyer::find($get('flyer_id'))) {
                            $set('total_price', $state * $flyer->price);
                        }
                    }),

                Forms\Components\TextInput::make('total_price')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(),

                Forms\Components\TextInput::make('customer_name')->required(),
                Forms\Components\TextInput::make('customer_phone')->required(),

                Forms\Components\Textarea::make('receiver_address')
                    ->label('Receiver Address')
                    ->rows(2),

                Forms\Components\Select::make('area_id')
                    ->label('Area')
                    ->options(Area::pluck('name', 'id'))
                    ->searchable(),

                Forms\Components\Select::make('city_id')
                    ->label('City')
                    ->options(City::pluck('name', 'id'))
                    ->searchable(),

                Forms\Components\TextInput::make('delivery_cost')
                    ->numeric()
                    ->default(0),

                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'out_for_delivery' => 'Out for delivery',
                        'delivered' => 'Delivered',
                    ])
                    ->default('pending'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('quantity'),
                Tables\Columns\TextColumn::make('total_price')->money('egp'),
                Tables\Columns\TextColumn::make('customer_name'),
                Tables\Columns\TextColumn::make('customer_phone'),
                Tables\Columns\TextColumn::make('receiver_address')->limit(20),
                Tables\Columns\TextColumn::make('area.name')->label('Area'),
                Tables\Columns\TextColumn::make('delivery_cost')->money('egp'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'out_for_delivery',
                        'success' => 'delivered',
                    ]),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFlyerOrders::route('/'),
            'create' => Pages\CreateFlyerOrder::route('/create'),
            'edit' => Pages\EditFlyerOrder::route('/{record}/edit'),
        ];
    }
}
