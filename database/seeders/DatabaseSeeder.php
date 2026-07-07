<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'user@test.com',
            'password' => Hash::make(env('APP_TEST_PASSWORD') ?? 'test123'),
            'role_id' => 2,
        ]);

        User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@test.com',
            'password' => Hash::make(env('APP_ADMIN_PASSWORD') ?? 'admin321'),
            'role_id' => 1,
        ]);
    }
}
