<?php

namespace App\Filament\Pages;

use App\Models\InsurancePackage;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class InsurancePackagess extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static string $view = 'filament.pages.insurance-packagess';

    protected static ?string $title = 'Insurance';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Insurance';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check()
            && in_array(auth()->user()->management, [
                'admin',
                'shipper',
            ]);
    }

    public $packages;

    public function mount(): void
    {
        $this->packages = InsurancePackage::where('is_active', true)->get();
    }

    public function subscribe($packageId)
    {
        $user = Auth::user();

        $user->insurance_package_id = $packageId;

        $user->save();

        Notification::make()
            ->title('Insurance activated successfully.')
            ->success()
            ->send();

        $this->mount();
    }
}
