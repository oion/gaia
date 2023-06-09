<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Customer::factory()
            ->count(5)
            ->hasDossiers(5)
            ->create();
        Customer::factory()
            ->count(15)
            ->hasDossiers(3)
            ->create();
        Customer::factory()
            ->count(1)
            ->hasDossiers(0)
            ->create();
    }
}
