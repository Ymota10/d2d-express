<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseItemResource\Pages;
use App\Models\WarehouseItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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

    /**
     * ✅ Non-admins only see their own warehouse items
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        if ($user?->management !== 'admin') {
            $query->where('user_id', $user->id);
        }

        return $query;
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
            Forms\Components\Select::make('user_id')
                ->label('Shipper')
                ->relationship('user', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->visible(fn () => auth()->user()?->management === 'admin'),

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

                // ✅ Admin only
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Shipper')
                    ->sortable()
                    ->visible(fn () => auth()->user()?->management === 'admin'),

                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('sku')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->searchable(),

                // ✅ Admin only
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->visible(fn () => auth()->user()?->management === 'admin'),
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
            'index' => Pages\ListWarehouseItem::route('/'),
            'create' => Pages\CreateWarehouseItem::route('/create'),
            'edit' => Pages\EditWarehouseItem::route('/{record}/edit'),
        ];
    }
}
