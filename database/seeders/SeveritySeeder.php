<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeveritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $severities = [
            ['id' => 1, 'name' => 'Low', 'level' => 10],
            ['id' => 2, 'name' => 'Medium', 'level' => 30],
            ['id' => 3, 'name' => 'High', 'level' => 60],
            // ['id' => 4, 'name' => 'Critical', 'level' => 100],
        ];

        foreach ($severities as $severity) {
            DB::table('severities')->updateOrInsert(
                ['id' => $severity['id']],
                [
                    'name' => $severity['name'],
                    'level' => $severity['level'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
