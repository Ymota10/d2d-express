<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseItemResource\Pages;
use App\Models\WarehouseItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WarehouseItemResource extends Resource
{
    protected static ?string $model = WarehouseItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Warehousing';

    protected static ?string $navigationLabel = 'Warehouse Items';

    /**
     * ✅ ONLY SHOW IN SIDEBAR IF USER HAS WAREHOUSING ENABLED
     */
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

            Forms\Components\TextInput::make('sku')
                ->label('SKU')
                ->unique(ignoreRecord: true)
                ->required(),

            Forms\Components\TextInput::make('name')
                ->label('Item Name')
                ->required(),

            Forms\Components\Textarea::make('description')
                ->label('Description')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Shipper')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sku')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('description')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->actions([
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
            'index' => Pages\ListWarehouseItem::route('/'),
            'create' => Pages\CreateWarehouseItem::route('/create'),
            'edit' => Pages\EditWarehouseItem::route('/{record}/edit'),
        ];
    }
}
