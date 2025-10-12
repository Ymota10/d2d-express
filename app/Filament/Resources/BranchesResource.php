<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BranchesResource\Pages;
use App\Models\Branches;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BranchesResource extends Resource
{
    protected static ?string $model = Branches::class;

    protected static ?string $navigationIcon = 'fluentui-branch-fork-16-o';

    public static function getNavigationBadge(): ?string
    {
        return (string) Branches::count();
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return $user->isAdmin(); // only admins see this menu
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('address')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('branch_type')
                    ->options([
                        'Casual Branch' => 'Casual Branch',
                        'Central Branch' => 'Central Branch',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('shipping_fees')
                    ->label('Shipping Fee')
                    ->numeric()
                    ->required()
                    ->default(0.00),
            ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('address')
                    ->searchable(),

                Tables\Columns\TextColumn::make('branch_type'),

                Tables\Columns\TextColumn::make('shipping_fees'),

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
            'index' => Pages\ListBranches::route('/'),
            'create' => Pages\CreateBranches::route('/create'),
            'edit' => Pages\EditBranches::route('/{record}/edit'),
        ];
    }
}
