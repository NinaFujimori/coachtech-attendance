<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdministoratersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'email' => 'admin@gmail.com',
            'password' =>'AdminPass', 
        ];
        DB::table('administoraters')->insert($param);
    }
}
