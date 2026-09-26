<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Fine-grained capabilities a label member can hold inside a label (tenant).
 *
 * Roles are mapped to permissions in {@see LabelRole::permissions()}; policies
 * only ever check permissions, never roles, so the matrix can evolve safely.
 */
enum LabelPermission: string
{
    case ViewCatalog = 'view_catalog';
    case ManageCatalog = 'manage_catalog';
    case ManageArtists = 'manage_artists';
    case ViewFinance = 'view_finance';
    case ManageFinance = 'manage_finance';
    case ManageMembers = 'manage_members';
    case ManageLabel = 'manage_label';
}
