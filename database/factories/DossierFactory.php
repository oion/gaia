<?php

namespace Database\Factories;

use App\Models\Dossier;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Dossier>
 */
class DossierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement('completed', 'ongoing', 'rejected');
        return [
            'customer_id' => Customer::factory(),
            'status' =>  $status,
            'status_date' => $this->faker->dateTimeThisYear()
        ];
    }
}
