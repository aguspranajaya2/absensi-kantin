<?php

namespace Database\Factories;

use App\Models\CanteenUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CanteenUser>
 */
class CanteenUserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'barcode_id' => strtoupper(fake()->unique()->bothify('KNT-###')),
            'name' => fake()->name(),
            'department' => fake()->randomElement(['Dapur', 'Kasir', 'Pelayanan']),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'is_active' => true,
        ];
    }
}
