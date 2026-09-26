<?php

declare(strict_types=1);

use App\Enums\LabelPermission;
use App\Enums\LabelRole;

it('grants every permission to the owner only', function (): void {
    foreach (LabelPermission::cases() as $permission) {
        expect(LabelRole::Owner->hasPermission($permission))->toBeTrue();
    }

    expect(LabelRole::Admin->hasPermission(LabelPermission::ManageLabel))->toBeFalse();
});

it('keeps finance out of reach of catalog managers and viewers', function (LabelRole $role): void {
    expect($role->hasPermission(LabelPermission::ViewFinance))->toBeFalse()
        ->and($role->hasPermission(LabelPermission::ManageFinance))->toBeFalse();
})->with([LabelRole::CatalogManager, LabelRole::Viewer]);

it('keeps viewers read-only', function (): void {
    expect(LabelRole::Viewer->permissions())->toBe([LabelPermission::ViewCatalog]);
});

it('lets accountants view but not edit the catalog', function (): void {
    expect(LabelRole::Accountant->hasPermission(LabelPermission::ViewCatalog))->toBeTrue()
        ->and(LabelRole::Accountant->hasPermission(LabelPermission::ManageCatalog))->toBeFalse();
});
