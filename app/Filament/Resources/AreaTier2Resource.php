<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AreaResource\Pages;
use App\Models\AreaTier2;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AreaTier2Resource extends Resource
{
    protected static ?string $model = AreaTier2::class;

    protected static ?string $navigationIcon = 'fluentui-globe-surface-20-o';

    protected static bool $shouldRegisterNavigation = false;

    public static function getNavigationBadge(): ?string
    {
        return (string) AreaTier2::count();
    }

    // public static function shouldRegisterNavigation(): bool
    // {
    //     $user = auth()->user();

    //     return $user->isAdmin(); // only admins see this menu
    // }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('name_ar')
                    ->maxLength(255),
                Forms\Components\Select::make('city_id')
                    ->relationship('city', 'name')
                    ->native(false)
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('delivery_cost')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('return_cost')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('replacement_partial_delivery_cost')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('overweight_cost')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('refund_cost')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('exchange_cost')
                    ->required()
                    ->numeric()
                    ->default(0.00),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name_ar')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('delivery_cost')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('return_cost')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('replacement_partial_delivery_cost')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('overweight_cost')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('refund_cost')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('exchange_cost')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAreas::route('/'),
            'create' => Pages\CreateArea::route('/create'),
            'edit' => Pages\EditArea::route('/{record}/edit'),
        ];
    }
}
