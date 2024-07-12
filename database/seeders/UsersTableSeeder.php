<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([

            [
                'id' => 1,
                'name' => 'IT',
                'email' => 'it@ptmkm.co.id',
                'email_verified_at' => null,
                'password' => Hash::make('Password.1'), // Replace 'your_password_here' with the desired password
                'remember_token' => null,
                'role' => 'IT',
                'last_login' => '2023-08-15 11:38:49',
                'login_counter' => 1,
                'is_active' => '1',
                'created_at' => '2023-07-08 05:42:25',
                'updated_at' => '2023-08-15 11:38:49',
            ],
            [
                'id' => 2,
                'name' => 'Admin',
                'email' => 'admin@ptmkm.co.id',
                'email_verified_at' => null,
                'password' => Hash::make('Password.1'), // Replace 'your_password_here' with the desired password
                'remember_token' => null,
                'role' => 'Super Admin',
                'last_login' => '2023-08-15 11:38:49',
                'login_counter' => 1,
                'is_active' => '1',
                'created_at' => '2023-07-08 05:42:25',
                'updated_at' => '2023-08-15 11:38:49',
            ],
            [
                'id' => 3,
                'name' => 'User',
                'email' => 'user@ptmkm.co.id',
                'email_verified_at' => null,
                'password' => Hash::make('Password.1'), // Replace 'your_password_here' with the desired password
                'remember_token' => null,
                'role' => 'User',
                'last_login' => '2023-08-15 11:38:49',
                'login_counter' => 1,
                'is_active' => '1',
                'created_at' => '2023-07-08 05:42:25',
                'updated_at' => '2023-08-15 11:38:49',
            ]
        ]);
    }
}
