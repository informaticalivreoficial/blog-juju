<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => env('ADMIN_NOME'),
                'email' => env('ADMIN_EMAIL'),
                'email_verified_at' => now(),
                'password' => bcrypt(env('ADMIN_PASS')),
                'remember_token' => \Illuminate\Support\Str::random(10),                
                'created_at' => now(),//Data e hora Atual 
                'role' => 'ADMIN',
                'status' => 1
            ]            
        ]);

        //User::factory()->count(20)->create();
    }
}
