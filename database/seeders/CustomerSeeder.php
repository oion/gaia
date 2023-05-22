<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Dossier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Customer::factory()
            ->count(10)
            ->has(Dossier::factory()->count(3))
            ->create();
        Customer::factory()
            ->count(5)
            ->has(Dossier::factory()->count(1))
            ->create();
        Customer::factory()
            ->count(5)
            ->has(Dossier::factory()->count(0))
            ->create();
    }
}
