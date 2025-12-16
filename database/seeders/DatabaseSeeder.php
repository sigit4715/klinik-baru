<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Create admin user for login
        \App\Models\User::create([
            'name' => 'Admin Klinik',
            'email' => 'admin@klinik.com',
            'password' => bcrypt('password123'),
        ]);

        // Run clinic seeder
        $this->call([
            ClinicSeeder::class,
        ]);
    }
}
