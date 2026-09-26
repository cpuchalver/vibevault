<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Label\Tenancy\EditLabelProfile;
use App\Filament\Label\Tenancy\RegisterLabel;
use App\Models\Label;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class LabelPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('label')
            ->path('label')
            ->brandName('VibeVault')
            ->databaseTransactions()
            ->login()
            ->passwordReset()
            ->emailVerification()
            ->profile(isSimple: false)
            ->tenant(Label::class, slugAttribute: 'slug', ownershipRelationship: 'label')
            ->tenantRegistration(RegisterLabel::class)
            ->tenantProfile(EditLabelProfile::class)
            ->colors([
                'primary' => Color::Violet,
            ])
            ->discoverResources(in: app_path('Filament/Label/Resources'), for: 'App\Filament\Label\Resources')
            ->discoverPages(in: app_path('Filament/Label/Pages'), for: 'App\Filament\Label\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Label/Widgets'), for: 'App\Filament\Label\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
