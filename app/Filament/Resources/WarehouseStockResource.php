<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseStockResource\Pages;
use App\Models\WarehouseStock;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WarehouseStockResource extends Resource
{
    protected static ?string $model = WarehouseStock::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Warehousing';

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return $user && ($user->warehousing || $user->management === 'admin');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            // Forms\Components\TextInput::make('sku')
            //     ->required(),
            // Forms\Components\TextInput::make('product_name')
            //     ->required(),
            Forms\Components\Select::make('warehouse_item_id')
                ->label('Item')
                ->relationship('item', 'name')
                ->preload()
                ->searchable()
                ->required(),

            Forms\Components\TextInput::make('quantity')
                ->numeric()
                ->required(),

            Forms\Components\Select::make('user_id')
                ->label('Assigned User')
                ->relationship('user', 'name')
                ->preload()  // Preloads users in the dropdown
                ->searchable()
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            // Tables\Columns\TextColumn::make('product_name'),
            // Tables\Columns\TextColumn::make('sku'),
            Tables\Columns\TextColumn::make('quantity'),
            Tables\Columns\TextColumn::make('item.name')->label('Item')
                ->searchable(),
            Tables\Columns\TextColumn::make('user.name')->label('Assigned User'),
            Tables\Columns\BadgeColumn::make('quantity')
                ->colors([
                    'success' => fn ($state) => $state > 10,
                    'warning' => fn ($state) => $state <= 10 && $state > 0,
                    'danger' => fn ($state) => $state === 0,
                ])
                ->icons([
                    'heroicon-o-check-circle' => fn ($state) => $state > 10,
                    'heroicon-o-exclamation-circle' => fn ($state) => $state <= 10,
                ]),
        ])
            ->filters([
                //
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
            'index' => Pages\ListWarehouseStock::route('/'),
            'create' => Pages\CreateWarehouseStock::route('/create'),
            'edit' => Pages\EditWarehouseStock::route('/{record}/edit'),
        ];
    }
}
