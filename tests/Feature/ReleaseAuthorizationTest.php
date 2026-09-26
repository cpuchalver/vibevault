<?php

declare(strict_types=1);

use App\Enums\LabelRole;
use App\Enums\ReleaseStatus;
use App\Enums\ReleaseType;
use App\Filament\Label\Resources\Releases\Pages\CreateRelease;
use App\Filament\Label\Resources\Releases\Pages\EditRelease;
use App\Models\Band;
use App\Models\Label;
use App\Models\Release;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

function actingInLabelPanel(User $user, Label $label): void
{
    test()->actingAs($user);

    Filament::setCurrentPanel('label');
    Filament::setTenant($label);
    Filament::bootCurrentPanel();
}

it('forbids viewers from opening the create page', function (): void {
    $viewer = User::factory()->create();
    $label = Label::factory()->withMember($viewer, LabelRole::Viewer)->create();

    $this->actingAs($viewer)
        ->get("/label/{$label->slug}/releases/create")
        ->assertForbidden();
});

it('forbids viewers from editing a release', function (): void {
    $viewer = User::factory()->create();
    $label = Label::factory()->withMember($viewer, LabelRole::Viewer)->create();
    $release = Release::factory()->forBand(Band::factory()->for($label)->create())->create();

    $this->actingAs($viewer)
        ->get("/label/{$label->slug}/releases/{$release->getKey()}/edit")
        ->assertForbidden();
});

it('lets catalog managers create a release attached to the current label', function (): void {
    $manager = User::factory()->create();
    $label = Label::factory()->withMember($manager, LabelRole::CatalogManager)->create();
    $band = Band::factory()->for($label)->create();

    actingInLabelPanel($manager, $label);

    Livewire::test(CreateRelease::class)
        ->fillForm([
            'title' => 'New Album',
            'band_id' => $band->getKey(),
            'type' => ReleaseType::Album,
            'status' => ReleaseStatus::Draft,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Release::query()->withoutGlobalScopes()->firstWhere('title', 'New Album'))
        ->label_id->toBe($label->getKey())
        ->band_id->toBe($band->getKey());
});

it('rejects a band from another label submitted in the form', function (): void {
    $manager = User::factory()->create();
    $label = Label::factory()->withMember($manager, LabelRole::CatalogManager)->create();
    $foreignBand = Band::factory()->create();

    actingInLabelPanel($manager, $label);

    Livewire::test(CreateRelease::class)
        ->fillForm([
            'title' => 'Hijack',
            'band_id' => $foreignBand->getKey(),
            'type' => ReleaseType::Single,
            'status' => ReleaseStatus::Draft,
        ])
        ->call('create')
        ->assertHasFormErrors(['band_id']);

    expect(Release::query()->withoutGlobalScopes()->where('title', 'Hijack')->exists())->toBeFalse();
});

it('rejects moving an existing release to a band of another label', function (): void {
    $manager = User::factory()->create();
    $label = Label::factory()->withMember($manager, LabelRole::CatalogManager)->create();
    $release = Release::factory()->forBand(Band::factory()->for($label)->create())->create();
    $foreignBand = Band::factory()->create();

    actingInLabelPanel($manager, $label);

    Livewire::test(EditRelease::class, ['record' => $release->getKey()])
        ->fillForm(['band_id' => $foreignBand->getKey()])
        ->call('save')
        ->assertHasFormErrors(['band_id']);

    expect($release->fresh()->band_id)->not->toBe($foreignBand->getKey());
});

it('denies record-less abilities outside of a tenant context', function (): void {
    $owner = User::factory()->create();
    Label::factory()->withMember($owner, LabelRole::Owner)->create();

    expect($owner->can('create', Release::class))->toBeFalse()
        ->and($owner->can('viewAny', Release::class))->toBeFalse();
});

it('denies record abilities on another label even to an owner', function (): void {
    $owner = User::factory()->create();
    Label::factory()->withMember($owner, LabelRole::Owner)->create();
    $foreignRelease = Release::factory()->create();

    expect($owner->can('view', $foreignRelease))->toBeFalse()
        ->and($owner->can('update', $foreignRelease))->toBeFalse()
        ->and($owner->can('delete', $foreignRelease))->toBeFalse();
});
