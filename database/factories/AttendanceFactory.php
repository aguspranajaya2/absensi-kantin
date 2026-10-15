<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\CanteenUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'canteen_user_id' => CanteenUser::factory(),
            'user_name' => fake()->name(),
            'barcode_id' => strtoupper(fake()->bothify('KNT-###')),
            'attendance_date' => fake()->date(),
            'type' => fake()->randomElement(['Masuk', 'Pulang']),
            'source' => 'Scanner',
            'checked_at' => fake()->dateTimeThisMonth(),
            'legacy_id' => null,
        ];
    }
}
