<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseSubscriptionResource\Pages;
use App\Models\WarehouseSubscription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WarehouseSubscriptionResource extends Resource
{
    protected static ?string $model = WarehouseSubscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

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
            Forms\Components\Select::make('warehouse_plan_id')
                ->relationship('plan', 'name')
                ->required(),
            Forms\Components\DatePicker::make('start_date')->required(),

            Forms\Components\Select::make('status')
                ->options([
                    'active' => 'Active',
                    'paused' => 'Paused',
                    'cancelled' => 'Cancelled',
                ])
                ->default('active')
                ->required(),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('user.name')->label('Shipper'),
            Tables\Columns\TextColumn::make('plan.name'),
            Tables\Columns\TextColumn::make('start_date')->date(),
            Tables\Columns\BadgeColumn::make('status')
                ->colors([
                    'success' => 'active',
                    'danger' => 'cancelled',
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
            'index' => Pages\ListWarehouseSubscription::route('/'),
        ];
    }
}
