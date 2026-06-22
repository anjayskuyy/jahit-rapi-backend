<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akun login default untuk login.html.
        // GANTI PASSWORD INI setelah login pertama kali di production.
        User::firstOrCreate(
            ['email' => 'admin@jahitrapi.test'],
            ['name' => 'Admin Jahit Rapi', 'password' => 'password']
        );

        $this->call(DemoSeeder::class);
    }
}
