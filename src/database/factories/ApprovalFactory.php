<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Approval;
use App\Models\Attendance;

class ApprovalFactory extends Factory
{
    protected $model = Approval::class;

    public function definition()
    {
        // ここではデフォルト値を返す（attendance_idはここでは未指定）
        return [
            'attendance_id' => Attendance::inRandomOrder()->value('id'),
            'start' => '09:00:00',
            'finish' => '18:00:00',
            'rest_start' => '12:00:00',
            'rest_finish' => '13:00:00',
            'description' => $this->faker->sentence(),
        ];
    }

    public function forAttendance(Attendance $attendance)
    {
        $attendanceStart = strtotime($attendance->date . ' ' . $attendance->start);
        $attendanceFinish = strtotime($attendance->date . ' ' . $attendance->finish);

        $start = date('H:i:s', strtotime('+' . mt_rand(-30, 30) . ' minutes', $attendanceStart));
        $finish = date('H:i:s', strtotime('+' . mt_rand(-30, 30) . ' minutes', $attendanceFinish));

        $restStart = $this->faker->dateTimeBetween(
            date('Y-m-d H:i:s', $attendanceStart),
            date('Y-m-d H:i:s', strtotime('-1 hour', $attendanceFinish))
        );

        $restFinish = (clone $restStart)->modify('+' . mt_rand(30, 90) . ' minutes');

        // ✅ return $this に変更
        return $this->state([
            'attendance_id' => $attendance->id,
            'start' => $start,
            'finish' => $finish,
            'rest_start' => $restStart->format('H:i:s'),
            'rest_finish' => $restFinish->format('H:i:s'),
            'description' => $this->faker->sentence(),
        ]);
    }
}




