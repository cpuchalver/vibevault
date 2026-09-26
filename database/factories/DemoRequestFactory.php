<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CatalogueSize;
use App\Enums\DemoRequesterRole;
use App\Models\DemoRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DemoRequest>
 */
class DemoRequestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'company' => fake()->company(),
            'role' => fake()->randomElement(DemoRequesterRole::cases()),
            'catalogue_size' => fake()->randomElement(CatalogueSize::cases()),
            'message' => fake()->optional()->paragraph(),
            'consented_at' => now(),
            'ip_hash' => DemoRequest::hashIp(fake()->ipv4()),
        ];
    }
}
