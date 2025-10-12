<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'fluentui-people-team-28-o';

    public static function getNavigationBadge(): ?string
    {
        return (string) User::count();
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        return $user->isAdmin(); // only admins see this menu
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('management')
                ->label('Management Role')
                ->options([
                    'admin' => 'Admin',
                    'courier' => 'Courier',
                    'shipper' => 'Shipper',
                ])
                ->required(),

            Forms\Components\TextInput::make('name')
                ->label('Full Name')
                ->required(),

            Forms\Components\TextInput::make('phone')
                ->label('Primary Phone')
                ->required()
                ->tel(),

            Forms\Components\TextInput::make('phone_secondary')
                ->label('Secondary Phone')
                ->tel(),

            Forms\Components\TextInput::make('email')
                ->label('Email')
                ->email()
                ->required(),

            Forms\Components\TextInput::make('national_id')
                ->label('National ID')
                ->required()
                ->unique(ignoreRecord: true),

            Forms\Components\Select::make('city_id')
                ->label('City')
                ->relationship('city', 'name')
                ->preload()
                ->searchable()
                ->required(),

            Forms\Components\Select::make('branch_id')
                ->label('Branch')
                ->relationship('branch', 'name')
                ->preload()
                ->searchable()
                ->required(),

            Forms\Components\TextInput::make('address')
                ->label('Address')
                ->required(),

            Forms\Components\FileUpload::make('profile_photo')
                ->label('Profile Photo')
                ->image()
                ->directory('profile-photos')
                ->disk('public')
                ->visibility('public')
                ->preserveFilenames(false)
                ->imagePreviewHeight('100')
                ->maxSize(2048),

            Forms\Components\Select::make('gender')
                ->label('Gender')
                ->options([
                    'male' => 'Male',
                    'female' => 'Female',
                    'other' => 'Other',
                ]),

            Forms\Components\TextInput::make('password')
                ->label('Password')
                ->password()
                ->required(fn ($livewire) => $livewire instanceof Pages\CreateUser)
                ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                ->dehydrated(fn ($state) => filled($state)),

            Forms\Components\TextInput::make('password_confirmation')
                ->label('Confirm Password')
                ->password()
                ->same('password')
                ->required(fn ($livewire) => $livewire instanceof Pages\CreateUser)
                ->dehydrated(false),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('management')->label('Role')->sortable(),
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('Phone'),
                Tables\Columns\TextColumn::make('phone_secondary')->label('Secondary Phone'),
                Tables\Columns\TextColumn::make('email')->label('Email')->sortable(),
                Tables\Columns\TextColumn::make('national_id')->label('National ID'),
                Tables\Columns\TextColumn::make('city.name')->label('City'),
                Tables\Columns\TextColumn::make('branch.name')->label('Branch'),
                Tables\Columns\TextColumn::make('gender')->label('Gender'),
                Tables\Columns\ImageColumn::make('profile_photo')
                    ->label('Profile Photo')
                    ->circular()
                    ->size(50),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('management')
                    ->options([
                        'admin' => 'Admin',
                        'courier' => 'Courier',
                        'shipper' => 'Shipper',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

                Action::make('loginAs')
                    ->label('Login As')
                    ->icon('heroicon-m-finger-print')
                    ->color('primary')
                    ->visible(fn ($record) => auth()->user()->management === 'admin')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        // Save current admin ID so they can return later
                        Session::put('impersonator_id', Auth::id());

                        // Log in as selected user
                        Auth::guard(config('filament.auth.guard', 'web'))->login($record);

                        // Regenerate session to ensure security
                        Session::regenerate();

                        // Redirect to user's dashboard
                        return redirect()->route('filament.admin.pages.dashboard');
                    }),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
