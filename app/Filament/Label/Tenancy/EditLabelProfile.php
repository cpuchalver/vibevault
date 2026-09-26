<?php

declare(strict_types=1);

namespace App\Filament\Label\Tenancy;

use App\Policies\LabelPolicy;
use Filament\Pages\Tenancy\EditTenantProfile;
use Filament\Schemas\Schema;

/**
 * Access is authorized by {@see LabelPolicy::update()} (owner only)
 * through the parent `canView()`.
 */
class EditLabelProfile extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return __('Profil du label');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components(LabelForm::components());
    }
}
