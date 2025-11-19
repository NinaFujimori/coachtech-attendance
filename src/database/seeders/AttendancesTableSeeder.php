<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;
use Faker\Factory as Faker;

class AttendancesTableSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        foreach ([2, 3, 4] as $userId) {
            // faker で 8/1〜9/30 の間からユニークな40日を取得
            $dates = collect();
            while ($dates->count() < 40) {
                $randomDate = $faker->dateTimeBetween('2025-09-01', '2025-10-31')->format('Y-m-d');
                $dates->add($randomDate);
                $dates = $dates->unique();
            }

            foreach ($dates as $date) {
                // 出勤時間（8〜10時の間）を生成
                $start = Carbon::createFromFormat(
                    'Y-m-d H:i',
                    $date . ' ' . rand(8, 10) . ':' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT)
                );

                // 退勤時間（7〜9時間後）
                $finish = (clone $start)->addHours(rand(7, 9));

                // 勤怠データ作成
                $attendance = Attendance::factory()->create([
                    'user_id' => $userId,
                    'date' => $date,
                    'start' => $start->format('H:i:s'),
                    'finish' => $finish->format('H:i:s'),
                ]);


                // 実働時間（full）を再計算
                $attendance->update(['full' => $attendance->calculateFullTime()]);
            }
        }
    }
}