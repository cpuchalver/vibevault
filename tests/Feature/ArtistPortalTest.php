<?php

declare(strict_types=1);

use App\Models\Artist;
use App\Models\Release;
use App\Models\User;

beforeEach(function (): void {
    $this->artistUser = User::factory()->create();

    $this->artist = Artist::factory()->withPortalUser($this->artistUser)->create();
    $this->published = Release::factory()->forArtist($this->artist)->create(['title' => 'Published Single']);
    $this->draft = Release::factory()->forArtist($this->artist)->draft()->create(['title' => 'Secret Draft']);

    // Same label, different artist: must stay invisible too.
    $this->labelmate = Artist::factory()->for($this->artist->label)->create();
    $this->labelmateRelease = Release::factory()->forArtist($this->labelmate)->create(['title' => 'Labelmate Album']);
});

it('shows the artist their published releases only', function (): void {
    $this->actingAs($this->artistUser)
        ->get("/artist/{$this->artist->slug}/releases")
        ->assertOk()
        ->assertSee('Published Single')
        ->assertDontSee('Secret Draft')
        ->assertDontSee('Labelmate Album');
});

it('hides draft releases even when the url is known', function (): void {
    $this->actingAs($this->artistUser)
        ->get("/artist/{$this->artist->slug}/releases/{$this->draft->getKey()}")
        ->assertNotFound();
});

it('hides releases of other artists of the same label', function (): void {
    $this->actingAs($this->artistUser)
        ->get("/artist/{$this->artist->slug}/releases/{$this->labelmateRelease->getKey()}")
        ->assertNotFound();
});

it('refuses access to another artist portal', function (): void {
    $this->actingAs($this->artistUser)
        ->get("/artist/{$this->labelmate->slug}/releases")
        ->assertNotFound();
});

it('does not expose any write page to artists', function (): void {
    $this->actingAs($this->artistUser)
        ->get("/artist/{$this->artist->slug}/releases/{$this->published->getKey()}/edit")
        ->assertNotFound();
});

it('keeps artists out of the label back-office', function (): void {
    $this->actingAs($this->artistUser)
        ->get("/label/{$this->artist->label->slug}/releases")
        ->assertForbidden();
});
