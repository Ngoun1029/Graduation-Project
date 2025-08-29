<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed roles safely using firstOrCreate
        Role::factory()->create();

        // Create a default user
        \App\Models\User::factory()->create();
    }
}
