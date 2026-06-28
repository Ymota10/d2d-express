<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InsurancePackageResource\Pages;
use App\Models\InsurancePackage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InsurancePackageResource extends Resource
{
    protected static ?string $model = InsurancePackage::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 30;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public static function canCreate(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public static function canEdit($record): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public static function canDelete($record): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('name_ar')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('percentage')
                ->numeric()
                ->required()
                ->suffix('%'),

            Forms\Components\TextInput::make('minimum_fee')
                ->numeric()
                ->required()
                ->suffix('EGP'),

            Forms\Components\TextInput::make('max_compensation')
                ->numeric()
                ->required()
                ->suffix('EGP'),

            Forms\Components\Toggle::make('covers_loss'),

            Forms\Components\Toggle::make('covers_damage'),

            Forms\Components\Toggle::make('is_active'),

            Forms\Components\Toggle::make('is_default'),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('percentage')
                    ->suffix('%'),

                Tables\Columns\TextColumn::make('minimum_fee')
                    ->suffix(' EGP'),

                Tables\Columns\TextColumn::make('max_compensation')
                    ->suffix(' EGP'),

                Tables\Columns\IconColumn::make('covers_loss')
                    ->boolean(),

                Tables\Columns\IconColumn::make('covers_damage')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_default')
                    ->boolean(),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInsurancePackages::route('/'),
            'create' => Pages\CreateInsurancePackage::route('/create'),
            'edit' => Pages\EditInsurancePackage::route('/{record}/edit'),
        ];
    }
}
