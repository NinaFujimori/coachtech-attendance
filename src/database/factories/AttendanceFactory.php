<?php

namespace Database\Factories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $approvalId = $this->faker->numberBetween(0,2);

        //九月分の勤怠として作成
        $date = $this->faker->dateTimeBetween('2025-09-01 08:00:00', '2025-09-30 12:00:00');
        //dateを元に時間を作成
        $start = (clone $date);
        $finish = (clone $start)->modify('+'.mt_rand(4,8).' hours');

        return [
            'user_id' => $this->faker->numberBetween(1,3),
            'status_id' => $this->faker->numberBetween(1,4),
            'approval_id' => $approvalId,
            'start' => $start->format('H:i:s'),
            'finish' => $finish->format('H:i:s'),
            'date' => $start->format('Y-m-d'), 
            'description' => in_array($approvalId, [1,2]) ? $this->faker->sentence : null,
        ];
    }
}
