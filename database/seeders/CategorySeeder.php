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
        $category = ['Plant', 'Flower', 'Indoor plant', 'Outdoor plant', 'Indoor flower', 'Outdoor flower'];
        for ($i = 0; $i < 6; $i++) {
            DB::table('categories')->insert([
                'name' => $category[$i],
            ]);
        }
    }
}
