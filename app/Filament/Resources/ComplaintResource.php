<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComplaintResource\Pages;
use App\Models\Complaint;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ComplaintResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static ?string $navigationLabel = 'Complaints';

    protected static ?string $pluralLabel = 'Complaints';

    protected static ?string $modelLabel = 'Complaint';

    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();
        $count = Complaint::where('approved', false)->count();

        if ($user && $user->management === 'admin' && $count > 0) {
            return (string) $count;
        }

        return null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $user = Auth::user();

        if ($user && $user->management === 'admin' && Complaint::where('approved', false)->count() > 0) {
            return 'danger';
        }

        return null;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id')
                    ->default(fn () => Auth::id()),

                Forms\Components\Textarea::make('message')
                    ->label('Complaint Message')
                    ->required()
                    ->maxLength(1000),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                if (Auth::user()->management !== 'admin') {
                    $query->where('user_id', Auth::id());
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Submitted By')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('message')
                    ->label('Complaint')
                    ->wrap()
                    ->color(function ($record) {
                        return match (true) {
                            Auth::user()->management === 'admin' && $record->approved => 'third', // light green
                            default => 'fourth',
                        };
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->visible(fn ($record) => ! (Auth::user()->management === 'admin' && $record->approved)),

                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => ! (Auth::user()->management === 'admin' && $record->approved)),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => ! (Auth::user()->management === 'admin' && $record->approved)),

                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Complaint')
                    ->modalSubheading('Are you sure you want to approve this complaint? This will mark it as handled.')
                    ->modalButton('Yes, Approve')
                    ->visible(fn ($record) => Auth::user()->management === 'admin' && ! $record->approved)
                    ->action(function (Complaint $record, Tables\Actions\Action $action) {
                        $record->update(['approved' => true]);
                        // $action->refreshTable(); // Instant update
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function canCreate(): bool
    {
        return Auth::check() && Auth::user()->management !== 'admin';
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComplaints::route('/'),
            'create' => Pages\CreateComplaint::route('/create'),
            'edit' => Pages\EditComplaint::route('/{record}/edit'),
        ];
    }
}
