<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
            'name' => '管理者',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('AdminPass'), 
            'class' => 0, 
        ];
        DB::table('users')->insert($param);
        $param = [
            'name' => '田中太郎',
            'email' => 'staff01@gmail.com',
            'password' =>Hash::make('tanakapass'), 
            'class' => 1, 
        ];
        DB::table('users')->insert($param);
        $param = [
            'name' => '佐藤花子',
            'email' => 'staff02@gmail.com',
            'password' =>Hash::make('satopass'), 
            'class' => 1,
        ];
        DB::table('users')->insert($param);
        $param = [
            'name' => '高知鉄久',
            'email' => 'staff03@gmail.com',
            'password' =>Hash::make('koutipass'), 
            'class' => 1,
        ];
        DB::table('users')->insert($param);
    }
}
