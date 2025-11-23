<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentReportResource\Pages;
use App\Models\PaymentReport;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // ✅ move here

class PaymentReportResource extends Resource
{
    protected static ?string $model = PaymentReport::class;

    protected static ?string $navigationGroup = 'Finance';

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('user.name')->label('Created By'),
                Tables\Columns\TextColumn::make('shipper.name')->label('Shipper'),
                Tables\Columns\TextColumn::make('total_cod')->money('EGP'),
                Tables\Columns\TextColumn::make('total_delivery_cost')->money('EGP'),
                Tables\Columns\TextColumn::make('extra_fees')->money('EGP'),
                Tables\Columns\TextColumn::make('final_amount')->money('EGP'),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                // 🔍 Filter by Shipper
                Tables\Filters\SelectFilter::make('shipper_id')
                    ->label('Shipper')
                    ->options(
                        \App\Models\User::where('management', 'shipper')
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->visible(fn () => auth()->user()?->management === 'admin'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn ($record) => static::getUrl('view', ['record' => $record])),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = Auth::user();

        Log::info('Logged-in user ID: '.$user->id);

        if ($user && ! method_exists($user, 'isAdmin')) {
            Log::info('No isAdmin method, filtering by shipper_id='.$user->id);
            $query->where('shipper_id', $user->id);
        } elseif ($user && method_exists($user, 'isAdmin') && ! $user->isAdmin()) {
            Log::info('User is not admin, filtering by shipper_id='.$user->id);
            $query->where('shipper_id', $user->id);
        } else {
            Log::info('User is admin, showing all reports');
        }

        Log::info('Final query: '.$query->toSql());

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentReports::route('/'),
            'create' => Pages\CreatePaymentReport::route('/create'),
            'edit' => Pages\EditPaymentReport::route('/{record}/edit'),
            'view' => Pages\ViewPaymentReport::route('/{record}'),
        ];
    }
}
