<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShipperResource\Pages;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ShipperResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'Shipper';

    protected static ?string $pluralModelLabel = 'Shippers';

    protected static ?string $navigationIcon = 'iconpark-delivery-o';

    protected static ?string $navigationLabel = 'Shippers';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return $user && $user->management === 'admin'; // only admins see this menu
    }

    /**
     * Limit to only users where management = 'shipper'
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('management', 'shipper');
    }

    public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        // Since it's read-only, we don’t need a form schema
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('phone')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('national_id')->searchable(),
                Tables\Columns\TextColumn::make('city.name')->label('City')->sortable(),
                Tables\Columns\TextColumn::make('branch.name')->label('Branch')->sortable(),
                Tables\Columns\TextColumn::make('gender'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([])
            ->actions([
                // Tables\Actions\ViewAction::make(), // Only allow viewing
            ])
            ->bulkActions([]); // Remove bulk actions
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShippers::route('/'),
        ];
    }
}
