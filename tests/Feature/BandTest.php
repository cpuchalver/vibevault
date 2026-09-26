<?php

declare(strict_types=1);

use App\Enums\BandType;
use App\Enums\LabelRole;
use App\Filament\Label\Resources\Artists\Pages\CreateArtist;
use App\Filament\Label\Resources\Bands\Pages\CreateBand;
use App\Filament\Label\Resources\Bands\Pages\EditBand;
use App\Filament\Label\Resources\Bands\RelationManagers\MembersRelationManager;
use App\Models\Artist;
use App\Models\Band;
use App\Models\Label;
use App\Models\Release;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Database\QueryException;
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
        ->fillForm([
            'name' => 'New Collective',
            'type' => BandType::Collective,
            'members' => [$this->singer->getKey(), $this->drummer->getKey()],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $band = Band::query()->withoutGlobalScopes()->firstWhere('name', 'New Collective');

    expect($band->label_id)->toBe($this->label->getKey())
        ->and($band->members()->pluck('artists.id')->all())
        ->toEqualCanonicalizing([$this->singer->getKey(), $this->drummer->getKey()]);
});

it('requires at least one member when creating a band', function (): void {
    actingInLabel($this->manager, $this->label);

    Livewire::test(CreateBand::class)
        ->fillForm(['name' => 'Empty Band', 'type' => BandType::Band, 'members' => []])
        ->call('create')
        ->assertHasFormErrors(['members']);

    expect(Band::query()->withoutGlobalScopes()->where('name', 'Empty Band')->exists())->toBeFalse();
});

it('limits a solo project to a single member', function (): void {
    actingInLabel($this->manager, $this->label);

    Livewire::test(CreateBand::class)
        ->fillForm([
            'name' => 'Not So Solo',
            'type' => BandType::Solo,
            'members' => [$this->singer->getKey(), $this->drummer->getKey()],
        ])
        ->call('create')
        ->assertHasFormErrors(['members']);
});

it('rejects members from another label in the band form', function (): void {
    actingInLabel($this->manager, $this->label);

    Livewire::test(CreateBand::class)
        ->fillForm([
            'name' => 'Hijack Band',
            'type' => BandType::Band,
            'members' => [$this->singer->getKey(), $this->foreignArtist->getKey()],
        ])
        ->call('create')
        ->assertHasFormErrors(['members']);

    expect(Band::query()->withoutGlobalScopes()->where('name', 'Hijack Band')->exists())->toBeFalse();
});

it('refuses to turn a multi-member band into a solo project', function (): void {
    $this->band->members()->attach($this->drummer);

    actingInLabel($this->manager, $this->label);

    Livewire::test(EditBand::class, ['record' => $this->band->getKey()])
        ->fillForm(['type' => BandType::Solo])
        ->call('save')
        ->assertHasFormErrors(['type']);
});

it('refuses to remove the last member of a band', function (): void {
    $this->band->members()->detach($this->singer);
})->throws(LogicException::class);

it('refuses a second member in a solo project', function (): void {
    $solo = Band::factory()->solo($this->singer)->create();

    $solo->members()->attach($this->drummer);
})->throws(LogicException::class);

it('refuses to hard-delete the only member of a band', function (): void {
    $this->singer->forceDelete();
})->throws(LogicException::class);

it('hides the detach action on the last member', function (): void {
    actingInLabel($this->manager, $this->label);

    Livewire::test(MembersRelationManager::class, ['ownerRecord' => $this->band, 'pageClass' => EditBand::class])
        ->assertActionHidden(TestAction::make('detach')->table($this->singer));
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

it('refuses to hard-delete a band that still has releases', function (): void {
    Release::factory()->forBand($this->band)->create();

    $this->band->forceDelete();
})->throws(QueryException::class);

it('creates the solo project of a new artist when requested', function (): void {
    actingInLabel($this->manager, $this->label);

    Livewire::test(CreateArtist::class)
        ->fillForm(['name' => 'Newcomer', 'create_solo_project' => true])
        ->call('create')
        ->assertHasNoFormErrors();

    $artist = Artist::query()->firstWhere('name', 'Newcomer');
    $solo = $artist->bands()->sole();

    expect($solo->type)->toBe(BandType::Solo)
        ->and($solo->name)->toBe('Newcomer')
        ->and($solo->label_id)->toBe($this->label->getKey());
});

it('creates no band when the solo project option is off', function (): void {
    actingInLabel($this->manager, $this->label);

    Livewire::test(CreateArtist::class)
        ->fillForm(['name' => 'Session Player', 'create_solo_project' => false])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Artist::query()->firstWhere('name', 'Session Player')->bands()->exists())->toBeFalse();
});
