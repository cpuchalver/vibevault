<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReleaseStatus;
use App\Enums\ReleaseType;
use App\Models\Artist;
use App\Models\Release;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Release>
 */
class ReleaseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'artist_id' => Artist::factory(),
            'label_id' => fn (array $attributes): int => Artist::query()
                ->withoutGlobalScopes()
                ->findOrFail($attributes['artist_id'])
                ->label_id,
            'title' => fake()->sentence(3),
            'type' => fake()->randomElement(ReleaseType::cases()),
            'status' => ReleaseStatus::Released,
            'upc' => fake()->unique()->numerify('############'),
            'catalog_number' => fake()->unique()->bothify('VV-####'),
            'release_date' => fake()->dateTimeBetween('-3 years', '+6 months'),
        ];
    }

    public function forArtist(Artist $artist): static
    {
        return $this->state([
            'artist_id' => $artist->getKey(),
            'label_id' => $artist->label_id,
        ]);
    }

    public function draft(): static
    {
        return $this->state(['status' => ReleaseStatus::Draft]);
    }
}
