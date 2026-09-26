<?php

declare(strict_types=1);

use App\Models\Artist;
use App\Models\Band;
use App\Models\Release;
use App\Models\User;

beforeEach(function (): void {
    $this->artistUser = User::factory()->create();

    $this->artist = Artist::factory()->withPortalUser($this->artistUser)->create();
    $this->labelmate = Artist::factory()->for($this->artist->label)->create();

    // The artist plays in a band with a labelmate and has a solo project.
    $this->band = Band::factory()->for($this->artist->label)->withMembers([$this->artist, $this->labelmate])->create();
    $this->solo = Band::factory()->solo($this->artist)->create();

    $this->bandRelease = Release::factory()->forBand($this->band)->create(['title' => 'Band Album']);
    $this->soloRelease = Release::factory()->forBand($this->solo)->create(['title' => 'Solo Single']);
    $this->draft = Release::factory()->forBand($this->band)->draft()->create(['title' => 'Secret Draft']);

    // Same label, band the artist is not part of: must stay invisible.
    $this->otherBand = Band::factory()->solo($this->labelmate)->create();
    $this->otherRelease = Release::factory()->forBand($this->otherBand)->create(['title' => 'Labelmate Solo EP']);
});

it('shows the published releases of every band the artist belongs to', function (): void {
    $this->actingAs($this->artistUser)
        ->get("/artist/{$this->artist->slug}/releases")
        ->assertOk()
        ->assertSee('Band Album')
        ->assertSee('Solo Single')
        ->assertDontSee('Secret Draft')
        ->assertDontSee('Labelmate Solo EP');
});

it('keeps showing releases of a band the artist has left', function (): void {
    $this->band->members()->updateExistingPivot($this->artist, ['left_on' => now()->subYear()]);

    $this->actingAs($this->artistUser)
        ->get("/artist/{$this->artist->slug}/releases/{$this->bandRelease->getKey()}")
        ->assertOk();
});

it('hides draft releases even when the url is known', function (): void {
    $this->actingAs($this->artistUser)
        ->get("/artist/{$this->artist->slug}/releases/{$this->draft->getKey()}")
        ->assertNotFound();
});

it('hides releases of bands the artist is not part of', function (): void {
    $this->actingAs($this->artistUser)
        ->get("/artist/{$this->artist->slug}/releases/{$this->otherRelease->getKey()}")
        ->assertNotFound();
});

it('refuses access to another artist portal', function (): void {
    $this->actingAs($this->artistUser)
        ->get("/artist/{$this->labelmate->slug}/releases")
        ->assertNotFound();
});

it('does not expose any write page to artists', function (): void {
    $this->actingAs($this->artistUser)
        ->get("/artist/{$this->artist->slug}/releases/{$this->bandRelease->getKey()}/edit")
        ->assertNotFound();
});

it('keeps artists out of the label back-office', function (): void {
    $this->actingAs($this->artistUser)
        ->get("/label/{$this->artist->label->slug}/releases")
        ->assertForbidden();
});

it('authorizes release viewing through band membership only', function (): void {
    expect($this->artistUser->can('view', $this->bandRelease))->toBeTrue()
        ->and($this->artistUser->can('view', $this->soloRelease))->toBeTrue()
        ->and($this->artistUser->can('view', $this->draft))->toBeFalse()
        ->and($this->artistUser->can('view', $this->otherRelease))->toBeFalse()
        ->and($this->artistUser->can('update', $this->bandRelease))->toBeFalse();
});
