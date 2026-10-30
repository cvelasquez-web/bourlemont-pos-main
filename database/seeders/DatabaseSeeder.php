<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ejecutamos nuestro Seeder personalizado con los datos del POS
        $this->call(BourlemontSeeder::class);
    }
}