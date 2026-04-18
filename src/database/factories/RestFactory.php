<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $restStart = $this->faker->dateTimeBetween('11:00:00', '13:00:00')->format('H:i:s');
        $restEnd = $this->faker->dateTimeBetween('14:00:00', '16:00:00')->format('H:i:s');
        return [
            'rest_start' => $restStart,
            'rest_end' => $restEnd,
        ];
    }
}
