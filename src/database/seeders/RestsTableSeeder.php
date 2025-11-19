<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\Rest;

class RestsTableSeeder extends Seeder
{
    public function run()
    {
        Attendance::all()->each(function ($attendance) {

        // approval = 0の勤怠（通常）
            if ($attendance->approval === 0) {
                //approvalが０のデータのみ作成する

                $count = rand(0, 2);
                for ($i = 0; $i < $count; $i++) {
                    $start = sprintf('%02d:%02d:00', rand(9, 18), [0, 15, 30, 45][rand(0, 3)]);
                    $finish = date('H:i:s', strtotime($start . ' +' . rand(30, 90) . ' minutes'));

                    Rest::create([
                        'attendance_id' => $attendance->id,
                        'start' => $start,
                        'finish' => $finish,
                        'approval' => 0,
                    ]);
                }
            }

            // approval = 1 の勤怠（申請中）
            if ($attendance->approval === 1) {
                //approvalが０のデータとそれと少しずつ違う１のデータを作成する

                // まずベースの approval=0 休憩を作る
                $baseCount = rand(0, 2);
                $baseRests = [];
                for ($i = 0; $i < $baseCount; $i++) {
                    $start = sprintf('%02d:%02d:00', rand(9, 18), [0, 15, 30, 45][rand(0, 3)]);
                    $finish = date('H:i:s', strtotime($start . ' +' . rand(30, 90) . ' minutes'));

                    $baseRests[] = Rest::create([
                        'attendance_id' => $attendance->id,
                        'start' => $start,
                        'finish' => $finish,
                        'approval' => 0,
                    ]);
                }

                // ベースを複製して approval=1 の休憩を作る
                foreach ($baseRests as $baseRest) {
                    $startDiff = rand(-15, 15);
                    $endDiff = rand(-15, 15);
                    Rest::create([
                        'attendance_id' => $attendance->id,
                        'approval' => 1,
                        'start' => date('H:i:s', strtotime($baseRest->start . " {$startDiff} minutes")),
                        'finish' => date('H:i:s', strtotime($baseRest->finish . " {$endDiff} minutes")),
                    ]);
                }

                // 追加の休憩を作ることもある
                if (rand(0, 1)) {
                    $startMinutes = rand(240, 480);
                    $finishMinutes = $startMinutes + rand(30, 90);

                    $start = date('H:i:s', strtotime("09:00:00 +{$startMinutes} minutes"));
                    $finish = date('H:i:s', strtotime("09:00:00 +{$finishMinutes} minutes"));
                    
                    Rest::create([
                        'attendance_id' => $attendance->id,
                        'approval' => 1,
                        'start' => $start,
                        'finish' => $finish,
                    ]);
                }
            }
            if ($attendance->approval === 2) {
                //まったく同じデータをapprovalがそれぞれ０と２で作成する
                $count = rand(0, 2);
                for ($i = 0; $i < $count; $i++) {
                    $start = sprintf('%02d:%02d:00', rand(9, 18), [0, 15, 30, 45][rand(0, 3)]);
                    $finish = date('H:i:s', strtotime($start . ' +' . rand(30, 90) . ' minutes'));

                    Rest::create([
                        'attendance_id' => $attendance->id,
                        'start' => $start,
                        'finish' => $finish,
                        'approval' => 0,
                    ]);

                    Rest::create([
                        'attendance_id' => $attendance->id,
                        'start' => $start,
                        'finish' => $finish,
                        'approval' => 2,
                    ]);
                }

            }

        });
    }
}

