<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $categories = [
            ['id' => 1, 'name' => 'Malware'],
            ['id' => 2, 'name' => 'Phishing'],
            ['id' => 3, 'name' => 'Unauthorized Access'],
            ['id' => 4, 'name' => 'Data Breach'],
            ['id' => 5, 'name' => 'Vulnerability'],
            ['id' => 6, 'name' => 'DDoS'],
            // ['id' => 7, 'name' => 'Insider Threat'],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->updateOrInsert(
                ['id' => $category['id']],
                [
                    'name' => $category['name'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
