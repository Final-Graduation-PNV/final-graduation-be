<?php

namespace Database\Seeders;

use Faker\Generator as Faker;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        $user = User::create([
            'name' => 'user',
            'email' => 'user@gmail.com',
            'password' => Hash::make('@DThanh1508'),
            'email_verified' => true,
            'city' => 'Kon Tum',
            'address' => 'Dak Pung - Dak Ro Nga - Dak To - Kon Tum'
        ]);

        DB::table('role_user')->insert([
            'role_id' => 3,
            'user_id' => $user->id
        ]);
    }
}
