<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReleaseStatus;
use App\Enums\ReleaseType;
use App\Models\Band;
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
            'band_id' => Band::factory(),
            'label_id' => fn (array $attributes): int => Band::query()
                ->withoutGlobalScopes()
                ->findOrFail($attributes['band_id'])
                ->label_id,
            'title' => fake()->sentence(3),
            'type' => fake()->randomElement(ReleaseType::cases()),
            'status' => ReleaseStatus::Released,
            'upc' => fake()->unique()->numerify('############'),
            'catalog_number' => fake()->unique()->bothify('VV-####'),
            'release_date' => fake()->dateTimeBetween('-3 years', '+6 months'),
        ];
    }

    public function forBand(Band $band): static
    {
        return $this->state([
            'band_id' => $band->getKey(),
            'label_id' => $band->label_id,
        ]);
    }

    public function draft(): static
    {
        return $this->state(['status' => ReleaseStatus::Draft]);
    }
}
