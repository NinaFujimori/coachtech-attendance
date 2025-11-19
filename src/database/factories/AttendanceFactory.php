<?php

namespace Database\Factories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        $approvalId = $this->faker->numberBetween(0,2);

        return [
            'user_id' => 1, // Seeder側で上書きする
            'status' => 3,
            'approval' => $approvalId,
            'start' => '09:00:00', // Seeder側で上書きする
            'finish' => '18:00:00', // Seeder側で上書きする
            
            'date' => now()->toDateString(), // Seeder側で上書きする
            'description' => $approvalId === 2 ? '承認済み勤怠データ' : null,
        ];
    }
}
