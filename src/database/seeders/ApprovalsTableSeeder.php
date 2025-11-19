<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\Approval;
use Carbon\Carbon;

class ApprovalsTableSeeder extends Seeder
{
    public function run()
    {
        // approval=1 の勤怠のみ対象
        Attendance::where('approval', 1)->get()->each(function ($attendance){
            // 勤怠時間をベースに多少ズレた申請データを作る
            $start = Carbon::parse($attendance->start)->addMinutes(rand(-15, 15))->format('H:i:s');
            $finish = Carbon::parse($attendance->finish)->addMinutes(rand(-15, 15))->format('H:i:s');

            Approval::create([
                'attendance_id' => $attendance->id,
                'approval' => 1 ,// 申請中
                'start' => $start,
                'finish' => $finish,
                'description' => '勤怠修正申請データ',
            ]);
        });

        Attendance::where('approval', 2)->get()->each(function ($attendance) {
            Approval::create([
                'attendance_id' => $attendance->id,
                'approval' => 2 ,//承認済み
                'start' => $attendance->start,
                'finish' => $attendance->finish,
                'description' => '承認済み勤怠データ',
            ]);
        });
    }
}
