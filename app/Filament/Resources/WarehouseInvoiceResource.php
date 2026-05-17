<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseInvoiceResource\Pages;
use App\Models\WarehouseInvoice;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WarehouseInvoiceResource extends Resource
{
    protected static ?string $model = WarehouseInvoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Warehousing';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->management === 'admin';
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('billing_month'),
            Tables\Columns\TextColumn::make('total')->money('EGP'),
            Tables\Columns\BadgeColumn::make('status')
                ->colors([
                    'success' => 'paid',
                    'warning' => 'pending',
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWarehouseInvoice::route('/'),
        ];
    }
}
