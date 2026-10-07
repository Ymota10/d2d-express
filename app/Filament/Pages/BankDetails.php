<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class BankDetails extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static string $view = 'filament.pages.bank-details';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Bank Details';

    protected static ?int $navigationSort = 20;

    public ?array $data = [];

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check()
            && auth()->user()->isShipper();
    }

    public function mount(): void
    {
        $user = Auth::user();

        $this->form->fill([
            'bank_name' => $user->bank_name,
            'bank_account_name' => $user->bank_account_name,
            'bank_account_number' => $user->bank_account_number,
            'bank_iban' => $user->bank_iban,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Bank Details')
                    ->description(
                        'Keep your bank information accurate to ensure transfers are processed correctly.'
                    )
                    ->icon('heroicon-o-building-library')
                    ->schema([

                        Forms\Components\Placeholder::make('bank_warning')
                            ->label('')
                            ->content(
                                'Important: Changes to your bank details may affect upcoming transfers. '.
                                'Bank detail changes can delay transfers by 2–3 working days while the new information is verified.'
                            )
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('bank_name')
                            ->label('Bank Name')
                            ->placeholder('e.g. CIB, Banque Misr, NBE')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('bank_account_name')
                            ->label('Account Name')
                            ->placeholder('Exact name registered on the bank account')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('bank_account_number')
                            ->label('Account Number')
                            ->placeholder('Enter your bank account number')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('bank_iban')
                            ->label('IBAN')
                            ->placeholder('Enter your IBAN')
                            ->required()
                            ->maxLength(255)
                            ->dehydrateStateUsing(
                                fn ($state) => strtoupper($state)
                            ),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $user = Auth::user();

        $data = $this->form->getState();

        $user->update([
            'bank_name' => $data['bank_name'],
            'bank_account_name' => $data['bank_account_name'],
            'bank_account_number' => $data['bank_account_number'],
            'bank_iban' => $data['bank_iban'],
        ]);

        Notification::make()
            ->title('Bank details updated successfully')
            ->body(
                'Your updated bank information has been saved. '.
                'Please note that transfers may be delayed by 2–3 working days while the new details are verified.'
            )
            ->success()
            ->persistent()
            ->send();

        $this->form->fill([
            'bank_name' => $user->bank_name,
            'bank_account_name' => $user->bank_account_name,
            'bank_account_number' => $user->bank_account_number,
            'bank_iban' => $user->bank_iban,
        ]);
    }
}
