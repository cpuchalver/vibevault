<?php

declare(strict_types=1);

use App\Enums\LabelRole;
use App\Enums\MusicGroupType;
use App\Filament\Label\Resources\MusicGroups\Pages\CreateMusicGroup;
use App\Filament\Label\Resources\MusicGroups\Pages\EditMusicGroup;
use App\Filament\Label\Resources\MusicGroups\RelationManagers\MembersRelationManager;
use App\Models\Artist;
use App\Models\Label;
use App\Models\MusicGroup;
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
    $this->group = MusicGroup::factory()->for($this->label)->withMembers([$this->singer])->create(['name' => 'Own Band']);

    $this->otherLabel = Label::factory()->create();
    $this->foreignArtist = Artist::factory()->for($this->otherLabel)->create(['name' => 'Foreign Artist']);
    $this->foreignGroup = MusicGroup::factory()->for($this->otherLabel)->create(['name' => 'Foreign Band']);
});

it('lets an artist belong to several groups', function (): void {
    $sideProject = MusicGroup::factory()->for($this->label)->create();
    $sideProject->members()->attach($this->singer, ['role' => 'Chant']);

    expect($this->singer->musicGroups()->pluck('music_groups.id')->all())
        ->toEqualCanonicalizing([$this->group->getKey(), $sideProject->getKey()]);
});

it('refuses a membership across labels at model level', function (): void {
    $this->group->members()->attach($this->foreignArtist);
})->throws(LogicException::class);

it('refuses a membership that ends before it starts', function (): void {
    $this->group->members()->attach($this->drummer, ['joined_on' => '2024-01-01', 'left_on' => '2023-01-01']);
})->throws(LogicException::class);

it('lists only the groups of the current label', function (): void {
    $this->actingAs($this->manager)
        ->get("/label/{$this->label->slug}/music-groups")
        ->assertOk()
        ->assertSee('Own Band')
        ->assertDontSee('Foreign Band');
});

it('refuses to resolve a foreign group through the current label url', function (): void {
    $this->actingAs($this->manager)
        ->get("/label/{$this->label->slug}/music-groups/{$this->foreignGroup->getKey()}")
        ->assertNotFound();
});

it('lets catalog managers create a group in the current label', function (): void {
    actingInLabel($this->manager, $this->label);

    Livewire::test(CreateMusicGroup::class)
        ->fillForm(['name' => 'New Collective', 'type' => MusicGroupType::Collective])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(MusicGroup::query()->withoutGlobalScopes()->firstWhere('name', 'New Collective'))
        ->label_id->toBe($this->label->getKey());
});

it('forbids viewers from creating groups', function (): void {
    $viewer = User::factory()->create();
    $this->label->members()->attach($viewer, ['role' => LabelRole::Viewer]);

    $this->actingAs($viewer)
        ->get("/label/{$this->label->slug}/music-groups/create")
        ->assertForbidden();
});

it('attaches an artist of the same label with membership details', function (): void {
    actingInLabel($this->manager, $this->label);

    Livewire::test(MembersRelationManager::class, ['ownerRecord' => $this->group, 'pageClass' => EditMusicGroup::class])
        ->callAction(TestAction::make('attach')->table(), [
            'recordId' => $this->drummer->getKey(),
            'role' => 'Batterie',
            'joined_on' => '2022-03-01',
        ])
        ->assertHasNoFormErrors();

    expect($this->group->members()->whereKey($this->drummer)->first())
        ->pivot->role->toBe('Batterie');
});

it('rejects attaching an artist of another label', function (): void {
    actingInLabel($this->manager, $this->label);

    Livewire::test(MembersRelationManager::class, ['ownerRecord' => $this->group, 'pageClass' => EditMusicGroup::class])
        ->callAction(TestAction::make('attach')->table(), [
            'recordId' => $this->foreignArtist->getKey(),
        ])
        ->assertHasFormErrors(['recordId']);

    expect($this->group->members()->whereKey($this->foreignArtist)->exists())->toBeFalse();
});

it('hides membership write actions from viewers', function (): void {
    $viewer = User::factory()->create();
    $this->label->members()->attach($viewer, ['role' => LabelRole::Viewer]);

    actingInLabel($viewer, $this->label);

    Livewire::test(MembersRelationManager::class, ['ownerRecord' => $this->group, 'pageClass' => EditMusicGroup::class])
        ->assertActionHidden(TestAction::make('attach')->table())
        ->assertActionHidden(TestAction::make('detach')->table($this->singer));
});

it('denies group abilities on another label even to an owner', function (): void {
    $owner = User::factory()->create();
    Label::factory()->withMember($owner, LabelRole::Owner)->create();

    expect($owner->can('view', $this->group))->toBeFalse()
        ->and($owner->can('update', $this->group))->toBeFalse()
        ->and($owner->can('manageMembers', $this->group))->toBeFalse();
});
