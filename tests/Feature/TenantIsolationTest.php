<?php

declare(strict_types=1);

use App\Enums\LabelRole;
use App\Models\Artist;
use App\Models\Band;
use App\Models\Label;
use App\Models\Release;
use App\Models\User;

beforeEach(function (): void {
    $this->member = User::factory()->create();

    $this->ownLabel = Label::factory()->withMember($this->member, LabelRole::CatalogManager)->create();
    $this->ownArtist = Artist::factory()->for($this->ownLabel)->create(['name' => 'Own Artist']);
    $this->ownBand = Band::factory()->solo($this->ownArtist)->create();
    $this->ownRelease = Release::factory()->forBand($this->ownBand)->create(['title' => 'Own Release']);

    $this->otherLabel = Label::factory()->create();
    $this->otherArtist = Artist::factory()->for($this->otherLabel)->create(['name' => 'Foreign Artist']);
    $this->otherBand = Band::factory()->solo($this->otherArtist)->create();
    $this->otherRelease = Release::factory()->forBand($this->otherBand)->create(['title' => 'Foreign Release']);
});

it('lists only the releases of the current label', function (): void {
    $this->actingAs($this->member)
        ->get("/label/{$this->ownLabel->slug}/releases")
        ->assertOk()
        ->assertSee('Own Release')
        ->assertDontSee('Foreign Release');
});

it('lists only the artists of the current label', function (): void {
    $this->actingAs($this->member)
        ->get("/label/{$this->ownLabel->slug}/artists")
        ->assertOk()
        ->assertSee('Own Artist')
        ->assertDontSee('Foreign Artist');
});

it('refuses access to a label the user does not belong to', function (): void {
    $this->actingAs($this->member)
        ->get("/label/{$this->otherLabel->slug}/releases")
        ->assertNotFound();
});

it('refuses to resolve a foreign record through the current label url', function (string $path): void {
    $path = str_replace(
        ['{release}', '{artist}'],
        [$this->otherRelease->getKey(), $this->otherArtist->getKey()],
        $path,
    );

    $this->actingAs($this->member)
        ->get("/label/{$this->ownLabel->slug}/{$path}")
        ->assertNotFound();
})->with([
    'release view' => 'releases/{release}',
    'release edit' => 'releases/{release}/edit',
    'artist view' => 'artists/{artist}',
    'artist edit' => 'artists/{artist}/edit',
]);

it('refuses the label panel to users without membership', function (): void {
    $this->actingAs(User::factory()->create())
        ->get("/label/{$this->ownLabel->slug}")
        ->assertForbidden();
});

it('refuses the label panel to unverified users', function (): void {
    $unverified = User::factory()->unverified()->create();
    $this->ownLabel->members()->attach($unverified, ['role' => LabelRole::Owner]);

    $this->actingAs($unverified)
        ->get("/label/{$this->ownLabel->slug}")
        ->assertForbidden();
});

it('refuses to persist a release whose band belongs to another label', function (): void {
    Release::factory()->create([
        'band_id' => $this->otherBand->getKey(),
        'label_id' => $this->ownLabel->getKey(),
    ]);
})->throws(LogicException::class);
