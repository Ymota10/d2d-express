<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseSubscriptionResource\Pages;
use App\Models\WarehouseSubscription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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

    /**
     * ✅ Admin sees all
     * ✅ Shipper sees only his subscriptions
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        if ($user?->management === 'admin') {
            return $query;
        }

        return $query->where('user_id', $user->id);
    }

    /**
     * ✅ Create only for admin
     */
    public static function canCreate(): bool
    {
        return auth()->user()?->management === 'admin';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Select::make('user_id')
                ->label('Shipper')
                ->relationship('user', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->disabled(fn () => auth()->user()?->management !== 'admin'),

            Forms\Components\Select::make('warehouse_plan_id')
                ->relationship('plan', 'name')
                ->required(),

            Forms\Components\DatePicker::make('start_date')
                ->required(),

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
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Shipper'),

                Tables\Columns\TextColumn::make('plan.name'),

                Tables\Columns\TextColumn::make('start_date')
                    ->date(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'paused',
                        'danger' => 'cancelled',
                    ]),

            ])

            ->filters([
                //
            ])

            ->actions([

                /**
                 * ✅ Admin only
                 */
                Tables\Actions\ViewAction::make()
                    ->visible(fn () => auth()->user()?->management === 'admin'),

                Tables\Actions\EditAction::make()
                    ->visible(fn () => auth()->user()?->management === 'admin'),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()?->management === 'admin'),

            ])

            ->bulkActions([

                Tables\Actions\BulkActionGroup::make([

                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->management === 'admin'),

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
