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
            'name' => 'A Thanh',
            'email' => 'athanh2002kt@gmail.com',
            'password' => Hash::make('@DThanh1508'),
            'email_verified' => true,
            'city' => 'Kon Tum',
            'address' => 'Dak Pung - Dak Ro Nga - Dak To - Kon Tum',
        ]);

        Shop::create([
            'name' => 'Thanh Shop',
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

//        expressed
        $user1 = User::create([
            'name' => 'Ngô Tròn',
            'email' => 'tron.ngo23@student.passerellesnumeriques.org',
            'password' => Hash::make('@DThanh1508'),
            'email_verified' => true,
            'city' => 'Kon Tum',
            'address' => 'Dak Pung - Dak Ro Nga - Dak To - Kon Tum',
        ]);

        Shop::create([
            'name' => 'Ngô Tròn',
            'phone' => '982934861',
            'birth' => '2002-08-15',
            'gender' => 'male',
            'address' => 'Dak Pung - Dak Ro Nga - Dak To - Kon Tum',
            'city' => 'Kon Tum',
            'renewal' => false,
            'user_id' => $user1->id,
            'end_time' => Carbon::now()->subDay(3)
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
            'name' => 'Hồ Thị Duyệt',
            'email' => 'duyetho21@gmail.com',
            'password' => Hash::make('@DThanh1508'),
            'email_verified' => true,
            'city' => 'Kon Tum',
            'address' => 'Dak Pung - Dak Ro Nga - Dak To - Kon Tum',
        ]);

        Shop::create([
            'name' => 'Hồ Thị Duyệt',
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

        $user3 = User::create([
            'name' => 'A Đăm Thanh',
            'email' => 'thanh.a23@student.passerellesnumeriques.org',
            'password' => Hash::make('@DThanh1508'),
            'email_verified' => true,
            'city' => 'Kon Tum',
            'address' => 'Dak Pung - Dak Ro Nga - Dak To - Kon Tum',
        ]);

        Shop::create([
            'name' => 'A Đăm Thanh',
            'phone' => '982934861',
            'birth' => '2002-08-15',
            'gender' => 'male',
            'address' => 'Dak Pung - Dak Ro Nga - Dak To - Kon Tum',
            'city' => 'Kon Tum',
            'renewal' => false,
            'user_id' => $user3->id,
            'end_time' => Carbon::now()->subDay(3)
        ]);

        DB::table('role_user')->insert([
            'role_id' => 3,
            'user_id' => $user3->id
        ]);

        DB::table('role_user')->insert([
            'role_id' => 2,
            'user_id' => $user3->id
        ]);

        $user4 = User::create([
            'name' => 'Lê Văn Tiến',
            'email' => 'dtien.le23@student.passerellesnumeriques.org',
            'password' => Hash::make('@DThanh1508'),
            'email_verified' => true,
            'city' => 'Kon Tum',
            'address' => 'Dak Pung - Dak Ro Nga - Dak To - Kon Tum',
        ]);

        Shop::create([
            'name' => 'Lê Văn Tiến',
            'phone' => '982934861',
            'birth' => '2002-08-15',
            'gender' => 'male',
            'address' => 'Dak Pung - Dak Ro Nga - Dak To - Kon Tum',
            'city' => 'Kon Tum',
            'renewal' => false,
            'user_id' => $user4->id,
            'end_time' => Carbon::now()->subDay(3)
        ]);

        DB::table('role_user')->insert([
            'role_id' => 3,
            'user_id' => $user4->id
        ]);

        DB::table('role_user')->insert([
            'role_id' => 2,
            'user_id' => $user4->id
        ]);
    }
}
