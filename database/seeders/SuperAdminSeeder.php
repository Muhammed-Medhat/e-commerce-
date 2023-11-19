<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name'=>"muhammed medhat",
            'email'=>"muhammedmedhat02@gmail.com",
            'password' => Hash::make('123456789'),
            'gender' => 'male',
            'is_admin' => 1,
        ]);
    }
}
