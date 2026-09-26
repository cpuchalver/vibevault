<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\LabelRole;
use App\Models\Label;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Label>
 */
class LabelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company().' Records',
            'legal_name' => fake()->company().' SAS',
            'country' => fake()->countryCode(),
            'contact_email' => fake()->companyEmail(),
        ];
    }

    public function withMember(User $user, LabelRole $role = LabelRole::Owner): static
    {
        return $this->afterCreating(
            fn (Label $label) => $label->members()->attach($user, ['role' => $role]),
        );
    }
}
