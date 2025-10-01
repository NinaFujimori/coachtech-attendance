<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('statuses')->insert([
            [
                'name' => '勤務外',
            ],
            [
                'name' => '出勤中',
            ],
            [
                'name' => '休憩中',
            ],
            [
                'name' => '退勤済み',
            ],
        ]);
    }
}
