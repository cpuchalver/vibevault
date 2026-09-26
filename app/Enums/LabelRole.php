<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Role of a user inside a label (stored on the `label_user` pivot).
 */
enum LabelRole: string implements HasColor, HasLabel
{
    case Owner = 'owner';
    case Admin = 'admin';
    case CatalogManager = 'catalog_manager';
    case Accountant = 'accountant';
    case Viewer = 'viewer';

    public function getLabel(): string
    {
        return match ($this) {
            self::Owner => __('Propriétaire'),
            self::Admin => __('Administrateur'),
            self::CatalogManager => __('Gestionnaire catalogue'),
            self::Accountant => __('Comptable / Royalties'),
            self::Viewer => __('Lecture seule'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Owner => 'danger',
            self::Admin => 'warning',
            self::CatalogManager => 'primary',
            self::Accountant => 'success',
            self::Viewer => 'gray',
        };
    }

    /**
     * @return list<LabelPermission>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::Owner => LabelPermission::cases(),
            self::Admin => array_values(array_filter(
                LabelPermission::cases(),
                fn (LabelPermission $permission): bool => $permission !== LabelPermission::ManageLabel,
            )),
            self::CatalogManager => [
                LabelPermission::ViewCatalog,
                LabelPermission::ManageCatalog,
                LabelPermission::ManageArtists,
            ],
            self::Accountant => [
                LabelPermission::ViewCatalog,
                LabelPermission::ViewFinance,
                LabelPermission::ManageFinance,
            ],
            self::Viewer => [
                LabelPermission::ViewCatalog,
            ],
        };
    }

    public function hasPermission(LabelPermission $permission): bool
    {
        return in_array($permission, $this->permissions(), true);
    }
}
