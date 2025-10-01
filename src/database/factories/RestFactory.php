<?php

namespace Database\Factories;

use App\Models\Rest;
use Illuminate\Database\Eloquent\Factories\Factory;

class RestFactory extends Factory
{
    protected $model = Rest::class;

    public function definition()
    {
        // どの勤怠に紐づくかランダム取得
        $attendance = Attendance::inRandomOrder()->first();

        // 勤務時間内で休憩を作成
        $attendanceStart = strtotime($attendance->date . ' ' . $attendance->start);
        $attendanceFinish = strtotime($attendance->date . ' ' . $attendance->finish);

        // 勤務中のランダムな時間に休憩開始
        $restStart = $this->faker->dateTimeBetween(
            date('Y-m-d H:i:s', $attendanceStart),
            date('Y-m-d H:i:s', $attendanceFinish . ' -1 hour') // 終了の1時間前までに休憩開始
        );

        // 30分〜90分くらいの休憩にする
        $restFinish = (clone $restStart)->modify('+'.mt_rand(30,90).' minutes');

        return [
            'attendance_id' => $attendance->id,
            'start' => $restStart->format('H:i:s'),
            'finish' => $restFinish->format('H:i:s'),
        ];
    }
}
