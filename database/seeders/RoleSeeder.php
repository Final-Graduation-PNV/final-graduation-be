<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = ['admin', 'shop', 'user'];
        for ($i = 0; $i < 3; $i++) {
            DB::table('roles')->insert([
                'name' => $roles[$i],
                'description' => $roles[$i]
            ]);
        }
    }
}
