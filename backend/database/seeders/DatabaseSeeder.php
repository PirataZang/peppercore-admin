<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::updateOrCreate(
            ['email' => 'master@master.com'],
            [
                'name' => 'Master',
                'password' => 'bolinha123',
                'active' => true,
                'email_verified_at' => now(),
            ],
        );

        $this->call(ProjectSeeder::class);
    }
}
