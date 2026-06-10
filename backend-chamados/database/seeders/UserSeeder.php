<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->createMany([
            ['name' => 'Ana Lima',    'email' => 'ana@suporte.com',   'password' => bcrypt('password')],
            ['name' => 'Bruno Costa', 'email' => 'bruno@suporte.com', 'password' => bcrypt('password')],
            ['name' => 'Carla Dias',  'email' => 'carla@suporte.com', 'password' => bcrypt('password')],
        ]);
    }
}
