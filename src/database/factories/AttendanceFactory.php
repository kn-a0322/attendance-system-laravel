<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
            $date = $this->faker->dateTimeBetween('2026-04-01', '2026-04-30')->format('Y-m-d');
            $clockIn = $this->faker->dateTimeBetween('08:00:00', '10:00:00')->format('H:i:s');/*出勤時間を定義*/
            $clockOut = $this->faker->dateTimeBetween('16:00:00', '20:00:00')->format('H:i:s');/*退勤時間を定義*/

            return [
                'user_id' => User::factory(),
                'date' => $date,
                'clock_in' => $clockIn,
                'clock_out' => $clockOut,
                'status' => 3,
            ];
    }
}
