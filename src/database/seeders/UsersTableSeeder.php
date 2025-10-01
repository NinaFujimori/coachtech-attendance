<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'name' => '田中太郎',
            'email' => 'staff01@gmail.com',
            'password' =>'tanakapass', 
        ];
        DB::table('users')->insert($param);
        $param = [
            'name' => '佐藤花子',
            'email' => 'staff02@gmail.com',
            'password' =>'satoupass', 
        ];
        DB::table('users')->insert($param);
        $param = [
            'name' => '高知鉄久',
            'email' => 'staff01@gmail.com',
            'password' =>'koutipass', 
        ];
        DB::table('users')->insert($param);
    }
}
