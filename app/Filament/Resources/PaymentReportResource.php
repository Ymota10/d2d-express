<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentReportResource\Pages;
use App\Models\PaymentReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentReportResource extends Resource
{
    protected static ?string $model = PaymentReport::class;

    protected static ?string $navigationGroup = 'Finance';

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->management !== 'track_express';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('id')
                    ->label('Invoice ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Created By')
                    ->searchable()
                    ->visible(fn () => auth()->user()?->management === 'admin'),

                Tables\Columns\TextColumn::make('shipper.name')
                    ->label('Shipper')
                    ->searchable()
                    ->visible(fn () => auth()->user()?->management === 'admin'),

                Tables\Columns\TextColumn::make('total_cod')
                    ->label('Total COD')
                    ->money('EGP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_delivery_cost')
                    ->label('Total Delivery Cost')
                    ->money('EGP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_open_package_fees')
                    ->label('Open Package Fees')
                    ->money('EGP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_insurance_fees')
                    ->label('Insurance Fees')
                    ->money('EGP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('extra_fees')
                    ->label('Extra Fees')
                    ->money('EGP')
                    ->description('Minimum 20 EGP deducted per transaction')
                    ->sortable(),

                Tables\Columns\TextColumn::make('final_amount')
                    ->label('Net Amount')
                    ->money('EGP')
                    ->sortable(),

                /*
                 * =====================================================
                 * Transaction Reference Number
                 * =====================================================
                 */
                Tables\Columns\TextColumn::make(
                    'transaction_reference_number'
                )
                    ->label('Transaction Reference')
                    ->searchable()
                    ->placeholder('—')
                    ->copyable()
                    ->copyMessage('Transaction reference copied')
                    ->copyMessageDuration(1500),

                /*
                 * =====================================================
                 * Transfer Status
                 * =====================================================
                 */
                Tables\Columns\IconColumn::make('is_transferred')
                    ->label('Transferred')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
            ])

            ->filters([
                Tables\Filters\SelectFilter::make('shipper_id')
                    ->label('Shipper')
                    ->options(
                        \App\Models\User::where(
                            'management',
                            'shipper'
                        )->pluck('name', 'id')
                    )
                    ->searchable()
                    ->visible(
                        fn () => auth()->user()?->management === 'admin'
                    ),

                Tables\Filters\SelectFilter::make('is_transferred')
                    ->label('Transfer Status')
                    ->options([
                        1 => 'Transferred',
                        0 => 'Not Transferred',
                    ]),
            ])

            ->actions([

                /*
                 * =====================================================
                 * VIEW
                 * =====================================================
                 */
                Tables\Actions\ViewAction::make()
                    ->url(
                        fn ($record) => static::getUrl(
                            'view',
                            ['record' => $record]
                        )
                    ),

                /*
                 * =====================================================
                 * TRANSFER
                 * ADMIN ONLY
                 * =====================================================
                 */
                Tables\Actions\Action::make('transfer')
                    ->label('Transfer')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')

                    ->visible(
                        fn ($record) => auth()->user()?->isAdmin()
                            && ! $record->is_transferred
                    )

                    ->form([
                        Forms\Components\TextInput::make(
                            'transaction_reference_number'
                        )
                            ->label('Transaction Reference Number')
                            ->placeholder(
                                'Enter bank transfer reference number'
                            )
                            ->required()
                            ->maxLength(255)
                            ->autofocus(),
                    ])

                    ->requiresConfirmation()

                    ->modalHeading('Transfer Payment')

                    ->modalDescription(
                        'Enter the transaction reference number for this payment transfer.'
                    )

                    ->modalSubmitActionLabel('Confirm Transfer')

                    ->action(function (
                        $record,
                        array $data
                    ) {

                        /*
                         * Prevent transferring an already
                         * transferred report.
                         */
                        if ($record->is_transferred) {
                            Notification::make()
                                ->title('Already Transferred')
                                ->warning()
                                ->body(
                                    'This payment report has already been transferred.'
                                )
                                ->send();

                            return;
                        }

                        /*
                         * Save transaction reference and
                         * mark the payment as transferred.
                         */
                        $record->update([
                            'transaction_reference_number' => $data['transaction_reference_number'],

                            'is_transferred' => true,
                        ]);

                        Notification::make()
                            ->title('Payment Transferred')
                            ->success()
                            ->body(
                                'The payment was marked as transferred successfully.'
                            )
                            ->send();
                    }),

                /*
                 * =====================================================
                 * DELETE
                 * =====================================================
                 */
                Tables\Actions\DeleteAction::make()
                    ->visible(
                        fn () => auth()->user()?->isAdmin()
                    ),
            ])

            /*
             * =========================================================
             * GREEN ROW AFTER TRANSFER
             * =========================================================
             */
            ->recordClasses(
                fn ($record) => $record->is_transferred
                    ? 'bg-green-50 dark:bg-green-900/20'
                    : null
            );
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = Auth::user();

        Log::info(
            'Logged-in user ID: '.$user->id
        );

        if (
            $user &&
            ! method_exists($user, 'isAdmin')
        ) {
            Log::info(
                'No isAdmin method, filtering by shipper_id='.$user->id
            );

            $query->where(
                'shipper_id',
                $user->id
            );

        } elseif (
            $user &&
            method_exists($user, 'isAdmin') &&
            ! $user->isAdmin()
        ) {
            Log::info(
                'User is not admin, filtering by shipper_id='.$user->id
            );

            $query->where(
                'shipper_id',
                $user->id
            );

        } else {
            Log::info(
                'User is admin, showing all reports'
            );
        }

        Log::info(
            'Final query: '.$query->toSql()
        );

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
