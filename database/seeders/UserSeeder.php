<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $users = [
            [
                'id' => 1,
                'username' => 'admin',
                'name' => 'Administrator',
                'email' => 'admin@csirt.local',
                'password' => Hash::make('password'),
                'role_id' => 3,
                'is_active' => true,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'username' => 'csirt',
                'name' => 'CSIRT Team',
                'email' => 'csirt@csirt.local',
                'password' => Hash::make('password'),
                'role_id' => 2,
                'is_active' => true,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'username' => 'staff',
                'name' => 'Staff User',
                'email' => 'staff@csirt.local',
                'password' => Hash::make('password'),
                'role_id' => 1,
                'is_active' => true,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['id' => $user['id']],
                $user
            );
        }
    }
}
