<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed database untuk production.
     *
     * Untuk deploy ke server:
     *   php artisan migrate --force
     *   php artisan db:seed
     *
     * Untuk development dengan data dummy:
     *   php artisan db:seed --class=DemoSeeder
     */
    public function run(): void
    {
        $this->call(ProductionSeeder::class);
    }
}
