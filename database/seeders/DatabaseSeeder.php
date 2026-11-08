<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            SystemAdministratorSeeder::class,
            ReferenceDataSeeder::class,
        ]);

        // Last step: load the workflow definitions in config/workflows
        // (D-071, D-097). Safe to repeat: loaded versions are skipped (D-189).
        $status = Artisan::call('workflows:load');
        if (isset($this->command)) {
            $this->command->getOutput()->write(Artisan::output());
        }

        if ($status !== 0) {
            throw new RuntimeException('Loading the workflow definitions failed.');
        }
    }
}
