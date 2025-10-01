<?php

namespace Database\Seeders;

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
        $this->call([
            AdministratersTableSeeder::class,
            UsersTableSeeder::class,
            StatusesTableSeeder::class,
            ApprovalsTableSeeder::class,
            AttendancesTableSeeder::class,
            RestsTableSeeder::class, 
        ]);
    }
}
