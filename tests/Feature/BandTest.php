<?php

declare(strict_types=1);

use App\Enums\LabelRole;
use App\Enums\BandType;
use App\Filament\Label\Resources\Bands\Pages\CreateBand;
use App\Filament\Label\Resources\Bands\Pages\EditBand;
use App\Filament\Label\Resources\Bands\RelationManagers\MembersRelationManager;
use App\Models\Artist;
use App\Models\Label;
use App\Models\Band;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Livewire\Livewire;

function actingInLabel(User $user, Label $label): void
{
    test()->actingAs($user);

    Filament::setCurrentPanel('label');
    Filament::setTenant($label);
    Filament::bootCurrentPanel();
}

beforeEach(function (): void {
    $this->manager = User::factory()->create();
    $this->label = Label::factory()->withMember($this->manager, LabelRole::CatalogManager)->create();

    $this->singer = Artist::factory()->for($this->label)->create(['name' => 'Lead Singer']);
    $this->drummer = Artist::factory()->for($this->label)->create(['name' => 'Drummer']);
    $this->band = Band::factory()->for($this->label)->withMembers([$this->singer])->create(['name' => 'Own Band']);

    $this->otherLabel = Label::factory()->create();
    $this->foreignArtist = Artist::factory()->for($this->otherLabel)->create(['name' => 'Foreign Artist']);
    $this->foreignBand = Band::factory()->for($this->otherLabel)->create(['name' => 'Foreign Band']);
});

it('lets an artist belong to several bands', function (): void {
    $sideProject = Band::factory()->for($this->label)->create();
    $sideProject->members()->attach($this->singer, ['role' => 'Chant']);

    expect($this->singer->bands()->pluck('bands.id')->all())
        ->toEqualCanonicalizing([$this->band->getKey(), $sideProject->getKey()]);
});

it('refuses a membership across labels at model level', function (): void {
    $this->band->members()->attach($this->foreignArtist);
})->throws(LogicException::class);

it('refuses a membership that ends before it starts', function (): void {
    $this->band->members()->attach($this->drummer, ['joined_on' => '2024-01-01', 'left_on' => '2023-01-01']);
})->throws(LogicException::class);

it('lists only the bands of the current label', function (): void {
    $this->actingAs($this->manager)
        ->get("/label/{$this->label->slug}/bands")
        ->assertOk()
        ->assertSee('Own Band')
        ->assertDontSee('Foreign Band');
});

it('refuses to resolve a foreign band through the current label url', function (): void {
    $this->actingAs($this->manager)
        ->get("/label/{$this->label->slug}/bands/{$this->foreignBand->getKey()}")
        ->assertNotFound();
});

it('lets catalog managers create a band in the current label', function (): void {
    actingInLabel($this->manager, $this->label);

    Livewire::test(CreateBand::class)
        ->fillForm(['name' => 'New Collective', 'type' => BandType::Collective])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Band::query()->withoutGlobalScopes()->firstWhere('name', 'New Collective'))
        ->label_id->toBe($this->label->getKey());
});

it('forbids viewers from creating bands', function (): void {
    $viewer = User::factory()->create();
    $this->label->members()->attach($viewer, ['role' => LabelRole::Viewer]);

    $this->actingAs($viewer)
        ->get("/label/{$this->label->slug}/bands/create")
        ->assertForbidden();
});

it('attaches an artist of the same label with membership details', function (): void {
    actingInLabel($this->manager, $this->label);

    Livewire::test(MembersRelationManager::class, ['ownerRecord' => $this->band, 'pageClass' => EditBand::class])
        ->callAction(TestAction::make('attach')->table(), [
            'recordId' => $this->drummer->getKey(),
            'role' => 'Batterie',
            'joined_on' => '2022-03-01',
        ])
        ->assertHasNoFormErrors();

    expect($this->band->members()->whereKey($this->drummer)->first())
        ->pivot->role->toBe('Batterie');
});

it('rejects attaching an artist of another label', function (): void {
    actingInLabel($this->manager, $this->label);

    Livewire::test(MembersRelationManager::class, ['ownerRecord' => $this->band, 'pageClass' => EditBand::class])
        ->callAction(TestAction::make('attach')->table(), [
            'recordId' => $this->foreignArtist->getKey(),
        ])
        ->assertHasFormErrors(['recordId']);

    expect($this->band->members()->whereKey($this->foreignArtist)->exists())->toBeFalse();
});

it('hides membership write actions from viewers', function (): void {
    $viewer = User::factory()->create();
    $this->label->members()->attach($viewer, ['role' => LabelRole::Viewer]);

    actingInLabel($viewer, $this->label);

    Livewire::test(MembersRelationManager::class, ['ownerRecord' => $this->band, 'pageClass' => EditBand::class])
        ->assertActionHidden(TestAction::make('attach')->table())
        ->assertActionHidden(TestAction::make('detach')->table($this->singer));
});

it('denies band abilities on another label even to an owner', function (): void {
    $owner = User::factory()->create();
    Label::factory()->withMember($owner, LabelRole::Owner)->create();

    expect($owner->can('view', $this->band))->toBeFalse()
        ->and($owner->can('update', $this->band))->toBeFalse()
        ->and($owner->can('manageMembers', $this->band))->toBeFalse();
});
