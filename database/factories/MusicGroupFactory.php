<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MusicGroupType;
use App\Models\Artist;
use App\Models\Label;
use App\Models\MusicGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MusicGroup>
 */
class MusicGroupFactory extends Factory
{
    public function definition(): array
    {
        return [
            'label_id' => Label::factory(),
            'name' => 'The '.fake()->unique()->words(2, true),
            'type' => MusicGroupType::Band,
            'country' => fake()->countryCode(),
            'formed_on' => fake()->dateTimeBetween('-15 years', '-1 year'),
            'biography' => fake()->paragraph(),
        ];
    }

    /**
     * @param  iterable<Artist>  $artists
     */
    public function withMembers(iterable $artists): static
    {
        return $this->afterCreating(function (MusicGroup $group) use ($artists): void {
            foreach ($artists as $artist) {
                $group->members()->attach($artist, ['role' => fake()->randomElement(['Chant', 'Guitare', 'Basse', 'Batterie', 'Claviers'])]);
            }
        });
    }
}
