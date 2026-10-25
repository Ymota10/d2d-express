<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Products';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (Auth::user()?->isShipper()) {
            $query->where('users_id', Auth::id());
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Select::make('users_id')
                    ->label('Shipper')
                    ->options(
                        User::where('management', 'shipper')
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->default(
                        fn () => Auth::user()?->isShipper()
                            ? Auth::id()
                            : null
                    )
                    ->disabled(
                        fn () => Auth::user()?->isShipper()
                    )
                    ->dehydrated(true),

                Forms\Components\TextInput::make('sku_code')
                    ->label('SKU Code')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('sku_name')
                    ->label('SKU Name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('sku_description')
                    ->label('SKU Description')
                    ->required()
                    ->rows(4),

                Forms\Components\TextInput::make('sku_image_url')
                    ->label('SKU Image URL')
                    ->url()
                    ->maxLength(2048),

                Forms\Components\TextInput::make('price')
                    ->label('Price')
                    ->numeric()
                    ->minValue(0)
                    ->required(),

                Forms\Components\TextInput::make('merchant_promotional_price')
                    ->label('Promotional Price')
                    ->numeric()
                    ->minValue(0)
                    ->nullable()
                    ->helperText('Optional. Leave empty if there is no promotion.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Shipper')
                    ->searchable()
                    ->sortable()
                    ->visible(
                        fn () => Auth::user()?->isAdmin()
                    ),

                Tables\Columns\TextColumn::make('sku_code')
                    ->label('SKU Code')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sku_name')
                    ->label('SKU Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\ImageColumn::make('sku_image_url')
                    ->label('Image')
                    ->square(),

                Tables\Columns\TextColumn::make('price')
                    ->money('EGP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('merchant_promotional_price')
                    ->label('Promo Price')
                    ->money('EGP')
                    ->placeholder('—')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('users_id')
                    ->label('Shipper')
                    ->options(
                        User::where('management', 'shipper')
                            ->pluck('name', 'id')
                    )
                    ->visible(
                        fn () => Auth::user()?->isAdmin()
                    ),
            ])
            ->actions([
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
