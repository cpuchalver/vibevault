<?php

declare(strict_types=1);

use App\Enums\LabelRole;
use App\Enums\ReleaseStatus;
use App\Enums\ReleaseType;
use App\Filament\Label\Resources\Releases\Pages\CreateRelease;
use App\Filament\Label\Resources\Releases\Pages\EditRelease;
use App\Models\Artist;
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
    $release = Release::factory()->forArtist(Artist::factory()->for($label)->create())->create();

    $this->actingAs($viewer)
        ->get("/label/{$label->slug}/releases/{$release->getKey()}/edit")
        ->assertForbidden();
});

it('lets catalog managers create a release attached to the current label', function (): void {
    $manager = User::factory()->create();
    $label = Label::factory()->withMember($manager, LabelRole::CatalogManager)->create();
    $artist = Artist::factory()->for($label)->create();

    actingInLabelPanel($manager, $label);

    Livewire::test(CreateRelease::class)
        ->fillForm([
            'title' => 'New Album',
            'artist_id' => $artist->getKey(),
            'type' => ReleaseType::Album,
            'status' => ReleaseStatus::Draft,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Release::query()->withoutGlobalScopes()->firstWhere('title', 'New Album'))
        ->label_id->toBe($label->getKey())
        ->artist_id->toBe($artist->getKey());
});

it('rejects an artist from another label submitted in the form', function (): void {
    $manager = User::factory()->create();
    $label = Label::factory()->withMember($manager, LabelRole::CatalogManager)->create();
    $foreignArtist = Artist::factory()->create();

    actingInLabelPanel($manager, $label);

    Livewire::test(CreateRelease::class)
        ->fillForm([
            'title' => 'Hijack',
            'artist_id' => $foreignArtist->getKey(),
            'type' => ReleaseType::Single,
            'status' => ReleaseStatus::Draft,
        ])
        ->call('create')
        ->assertHasFormErrors(['artist_id']);

    expect(Release::query()->withoutGlobalScopes()->where('title', 'Hijack')->exists())->toBeFalse();
});

it('rejects moving an existing release to an artist of another label', function (): void {
    $manager = User::factory()->create();
    $label = Label::factory()->withMember($manager, LabelRole::CatalogManager)->create();
    $release = Release::factory()->forArtist(Artist::factory()->for($label)->create())->create();
    $foreignArtist = Artist::factory()->create();

    actingInLabelPanel($manager, $label);

    Livewire::test(EditRelease::class, ['record' => $release->getKey()])
        ->fillForm(['artist_id' => $foreignArtist->getKey()])
        ->call('save')
        ->assertHasFormErrors(['artist_id']);

    expect($release->fresh()->artist_id)->not->toBe($foreignArtist->getKey());
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
