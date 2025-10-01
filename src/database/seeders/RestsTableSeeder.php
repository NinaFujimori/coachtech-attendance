<?php

namespace Database\Seeders;

use App\Models\Attendance;
use Illuminate\Database\Seeder;

class RestsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Attendance::all()->each(function ($attendance) {
            // 各勤怠に 0〜2件 の休憩をランダム作成
            Rest::factory()->count(rand(0,2))->create([
                'attendance_id' => $attendance->id,
            ]);
        });
    }
}
