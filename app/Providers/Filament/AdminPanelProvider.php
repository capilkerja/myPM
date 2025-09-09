<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Forms\Components\FileUpload;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use Devonab\FilamentEasyFooter\EasyFooterPlugin;
use Filament\Actions\Action;
use Illuminate\Support\HtmlString;
use Orion\FilamentGreeter\GreeterPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->spa()
            ->databaseTransactions()
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->registration()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                GreeterPlugin::make()
                    ->message(text: fn() => auth()->user()->hasRole('super_admin') ? __('Welcome my lord') : __('Welcome'))
                    ->name(fn() => auth()->user()->name)
                    ->title('MyDukcapil adalah aplikasi manajemen project yang bertujuan untuk memudahkan dalam tracking setiap kegiatan/ticket/tugas untuk mendukung tim Anda dalam mencapai tujuan Instansi Anda. Selamat tinggal lupa tugas.')
                    // ->avatar(size: 'w-16 h-16', url: 'https://randomuser.me/api/portraits/men/1.jpg')
                    ->avatar(size: 'w-16 h-16', enabled: true)
                    ->action(
                        Action::make('action')
                            ->label('Hubungi Developer')
                            ->icon('heroicon-o-chat-bubble-bottom-center')
                            ->url('https://wa.me/6282137753892')
                    )
                    ->sort(-2)
                    ->timeSensitive(morningStart: 6, afternoonStart: 12, eveningStart: 15, nightStart: 18)
                    ->columnSpan('full'),
                EasyFooterPlugin::make()
                    ->withSentence(new HtmlString('MyDukcapil v' . config('app.version') . ' - Made with ❤️ by <a href="#">RzPahlavi</a>'))
                    ->withLoadTime('This page loaded in'),
                BreezyCore::make()
                    ->myProfile(
                        shouldRegisterUserMenu: true, // Sets the 'account' link in the panel User Menu (default = true)
                        userMenuLabel: 'My Profile', // Customizes the 'account' link label in the panel User Menu (default = null)
                        shouldRegisterNavigation: true, // Adds a main navigation item for the My Profile page (default = false)
                        navigationGroup: 'Settings', // Sets the navigation group for the My Profile page (default = null)
                        hasAvatars: true, // Enables the avatar upload form component (default = false)
                        slug: 'my-profile' // Sets the slug for the profile page (default = 'my-profile')
                    )
                    ->avatarUploadComponent(fn($fileUpload) => $fileUpload->disableLabel())
                    // OR, replace with your own component
                    ->avatarUploadComponent(fn() => FileUpload::make('avatar_url')->disk('public'))
                    ->enableTwoFactorAuthentication(
                        force: false,
                    )
                    ->enableBrowserSessions(condition: true),
                FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->passwordReset()
            ->emailVerification();
        // ->profile();
    }
}
