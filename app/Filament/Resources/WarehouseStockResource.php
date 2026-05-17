<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseStockResource\Pages;
use App\Models\WarehouseStock;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

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

    /**
     * ✅ Only admins can create
     */
    public static function canCreate(): bool
    {
        return auth()->user()?->management === 'admin';
    }

    /**
     * ✅ Only admins can edit
     */
    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->management === 'admin';
    }

    /**
     * ✅ Only admins can delete
     */
    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->management === 'admin';
    }

    /**
     * ✅ Only admins can bulk delete
     */
    public static function canDeleteAny(): bool
    {
        return auth()->user()?->management === 'admin';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('warehouse_item_id')
                ->label('Item')
                ->relationship(
                    'item',
                    'name',
                    modifyQueryUsing: function ($query) {
                        $user = auth()->user();

                        if ($user?->management !== 'admin') {
                            $query->where('user_id', $user->id);
                        }
                    }
                )
                ->preload()
                ->searchable()
                ->required(),

            Forms\Components\TextInput::make('quantity')
                ->numeric()
                ->required(),

            Forms\Components\Select::make('user_id')
                ->label('Assigned User')
                ->relationship('user', 'name')
                ->preload()
                ->searchable()
                ->required()
                ->visible(fn () => auth()->user()?->management === 'admin'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('quantity'),

                Tables\Columns\TextColumn::make('item.name')
                    ->label('Item')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Assigned User')
                    ->visible(fn () => auth()->user()?->management === 'admin'),

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
            'index' => Pages\ListWarehouseStock::route('/'),
            'create' => Pages\CreateWarehouseStock::route('/create'),
            'edit' => Pages\EditWarehouseStock::route('/{record}/edit'),
        ];
    }
}
