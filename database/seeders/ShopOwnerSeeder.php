<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ShopOwnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'shop',
            'email' => 'athanh2002kt@gmail.com',
            'password' => Hash::make('@DThanh1508'),
            'email_verified' => true,
            'city' => 'Kon Tum',
            'address' => 'Dak Pung - Dak Ro Nga - Dak To - Kon Tum',
        ]);

        Shop::create([
            'name' => 'shop',
            'phone' => '982934861',
            'birth' => '2002-08-15',
            'gender' => 'male',
            'address' => 'Dak Pung - Dak Ro Nga - Dak To - Kon Tum',
            'city' => 'Kon Tum',
            'user_id' => $user->id,
            'end_time' => Carbon::now()->addMonth(2)->format('Y-m-d')
        ]);

        DB::table('role_user')->insert([
            'role_id' => 3,
            'user_id' => $user->id
        ]);

        DB::table('role_user')->insert([
            'role_id' => 2,
            'user_id' => $user->id
        ]);

        $user1 = User::create([
            'name' => 'shop1',
            'email' => 'shop1@gmail.com',
            'password' => Hash::make('@DThanh1508'),
            'email_verified' => true,
            'city' => 'Kon Tum',
            'address' => 'Dak Pung - Dak Ro Nga - Dak To - Kon Tum',
        ]);

        Shop::create([
            'name' => 'shop1',
            'phone' => '982934861',
            'birth' => '2002-08-15',
            'gender' => 'male',
            'address' => 'Dak Pung - Dak Ro Nga - Dak To - Kon Tum',
            'city' => 'Kon Tum',
            'renewal' => false,
            'user_id' => $user1->id,
            'end_time' => Carbon::now()
        ]);

        DB::table('role_user')->insert([
            'role_id' => 3,
            'user_id' => $user1->id
        ]);

        DB::table('role_user')->insert([
            'role_id' => 2,
            'user_id' => $user1->id
        ]);

        $user2 = User::create([
            'name' => 'shop2',
            'email' => 'shop2@gmail.com',
            'password' => Hash::make('@DThanh1508'),
            'email_verified' => true,
            'city' => 'Kon Tum',
            'address' => 'Dak Pung - Dak Ro Nga - Dak To - Kon Tum',
        ]);

        Shop::create([
            'name' => 'shop2',
            'phone' => '982934861',
            'birth' => '2002-08-15',
            'gender' => 'male',
            'address' => 'Dak Pung - Dak Ro Nga - Dak To - Kon Tum',
            'city' => 'Kon Tum',
            'renewal' => false,
            'user_id' => $user2->id,
            'end_time' => Carbon::now()->subDay(3)
        ]);

        DB::table('role_user')->insert([
            'role_id' => 3,
            'user_id' => $user2->id
        ]);

        DB::table('role_user')->insert([
            'role_id' => 2,
            'user_id' => $user2->id
        ]);
    }
}
