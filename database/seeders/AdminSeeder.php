<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Admin',
            'email' => 'dellyit001@gmail.com',
            'phone' => '0724437239',
            'usertype' => 'admin',
            'status' => 'active',
            'is_suspended' => 'no',
            'password' => Hash::make('Admin123@'),
            'last_active_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('Admin user created successfully.');
    }
}