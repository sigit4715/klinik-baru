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
        $admin = new \App\Models\User([
            'name' => 'Admin Klinik',
            'email' => 'admin@klinik.com',
            'password' => bcrypt('password123'),
        ]);
        $admin->role = \App\Models\Role::ADMIN;
        $admin->is_active = true;
        $admin->save();

        // Run clinic seeder
        $this->call([
            ClinicSeeder::class,
        ]);
    }
}
