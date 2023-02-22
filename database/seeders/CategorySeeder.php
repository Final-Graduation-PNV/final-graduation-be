<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $category = ['plant', 'flower', 'indoor plant', 'outdoor plant', 'indoor flower', 'outdoor flower'];
        for ($i = 0; $i < 6; $i++) {
            DB::table('categories')->insert([
                'name' => $category[$i],
            ]);
        }
    }
}
