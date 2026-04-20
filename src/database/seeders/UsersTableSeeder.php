<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'name' => '山田  太郎',
                'email' => 'test1@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 0,
            ],
            [
                'name' => '山田  花子',
                'email' => 'test2@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 0,
            ],
            [   
                'name' => '管理者',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 1,
            ],
        ];
        DB::table('users')->insert($users);

    }
}
