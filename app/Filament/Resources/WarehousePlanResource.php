<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehousePlanResource\Pages;
use App\Models\WarehousePlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WarehousePlanResource extends Resource
{
    protected static ?string $model = WarehousePlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Warehousing';

    protected static bool $shouldRegisterNavigation = true;

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return $user && ($user->warehousing || $user->management === 'admin');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\Select::make('billing_type')
                ->options([
                    'monthly' => 'Monthly Rent',
                    'per_order' => 'Per Successful Order',
                ])
                ->required(),
            Forms\Components\TextInput::make('monthly_price')
                ->numeric()
                ->visible(fn ($get) => $get('billing_type') === 'monthly'),
            Forms\Components\TextInput::make('order_fee')
                ->numeric()
                ->visible(fn ($get) => $get('billing_type') === 'per_order'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\BadgeColumn::make('billing_type')
                ->formatStateUsing(fn ($state) => match ($state) {
                    'monthly' => 'Rent',
                    'per_order' => 'Per Order Fee',
                    default => ucfirst($state),
                })
                ->colors([
                    'primary' => fn ($state) => $state === 'monthly',
                    'success' => fn ($state) => $state === 'per_order',
                ]),

            Tables\Columns\TextColumn::make('monthly_price')->money('EGP'),
            Tables\Columns\TextColumn::make('order_fee')->money('EGP'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWarehousePlan::route('/'),
            'create' => Pages\CreateWarehousePlan::route('/create'),
            'edit' => Pages\EditWarehousePlan::route('/{record}/edit'),
        ];
    }
}
