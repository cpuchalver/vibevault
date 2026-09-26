<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Artist;
use App\Models\Label;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Artist>
 */
class ArtistFactory extends Factory
{
    public function definition(): array
    {
        return [
            'label_id' => Label::factory(),
            'name' => fake()->unique()->name(),
            'legal_name' => fake()->name(),
            'country' => fake()->countryCode(),
            'isni' => fake()->numerify('################'),
            'biography' => fake()->paragraph(),
        ];
    }

    public function withPortalUser(User $user): static
    {
        return $this->afterCreating(fn (Artist $artist) => $artist->users()->attach($user));
    }
}
