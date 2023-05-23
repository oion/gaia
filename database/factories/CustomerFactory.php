<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array

    {
        // $faker = Faker\Factory::create('ro_RO');

        $type = $this->faker->randomElement(['I', 'B']);
        $name = $type === 'I' ? $this->faker->name() : $this->faker->company();



        return [
            'name' => $name,
            'type' => $type,
            'email' => $this->faker->email(),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'postal_code' => $this->faker->postCode(),
            'county' => fake(locale: 'ro_RO')->county(),
            'country_code' => $this->faker->countryCode(),

        ];
    }
}
