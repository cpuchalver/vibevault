<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\BandType;
use App\Models\Artist;
use App\Models\Band;
use App\Models\Label;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Band>
 */
class BandFactory extends Factory
{
    public function definition(): array
    {
        return [
            'label_id' => Label::factory(),
            'name' => 'The '.fake()->unique()->words(2, true),
            'type' => BandType::Band,
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
        return $this->afterCreating(function (Band $band) use ($artists): void {
            foreach ($artists as $artist) {
                $band->members()->attach($artist, ['role' => fake()->randomElement(['Chant', 'Guitare', 'Basse', 'Batterie', 'Claviers'])]);
            }
        });
    }
}
