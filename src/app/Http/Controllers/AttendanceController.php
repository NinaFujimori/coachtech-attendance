<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;

class AttendanceController extends Controller
{
    // 勤怠管理画面表示
    public function attendance(){
            
        // 現在日時を取得（タイムゾーンは config/app.php の timezone に基づく）
        $now = Carbon::now();

        $week = ['日', '月', '火', '水', '木', '金', '土'];
        $date = $now->format('Y年m月d日（') . $week[$now->dayOfWeek] . '）';

        $time = $now->format('H:i');

        // 今日の日付（Y-m-d形式）を取得
        $today = Carbon::today();

        // ログインユーザーIDを取得
        $userId = Auth::id();

        // ログインユーザーの今日の勤怠情報を取得
        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('date', $today)
            ->first();

        // 休憩の情報を取得
        $rests = $attendance ? Rest::where('attendance_id', $attendance->id)->get() : collect();

        return view('attendance', compact('date', 'time', 'attendance', 'rests'));
    }

    public function start(Request $request){
        $user = Auth::user();

        Attendance::create([
            'user_id' => $user->id,
            'status' => 0,  // 出勤中
            'approval' => 0,
            'start' => Carbon::now()->format('H:i:s'),
            'date' => Carbon::today(),
        ]);

        return redirect('/attendance');
    }

    public function restStart(Request $request){

        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('date', Carbon::today())
            ->first();

        if ($attendance) {
            Rest::create([
                'attendance_id' => $attendance->id,
                'start' => Carbon::now()->format('H:i:s'),
            ]);

            $attendance->update(['status' => 1]); // 休憩中
        }

        return redirect('/attendance');
    }

    public function restFinish(Request $request){
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('date', Carbon::today())
            ->first();

        if ($attendance) {
            $rest = Rest::where('attendance_id', $attendance->id)
                ->whereNull('finish')
                ->latest()
                ->first();

            if ($rest) {
                $rest->update(['finish' => Carbon::now()->format('H:i:s')]);
                $attendance->update(['status' => 0]); // 出勤中に戻す
            }
        }

        return redirect('/attendance');
    }

    public function finish(Request $request)
{
    $attendance = Attendance::where('user_id', Auth::id())
        ->whereDate('date', Carbon::today())
        ->first();

    if ($attendance) {
        $finishTime = Carbon::now();
        $startTime = Carbon::createFromFormat('H:i:s', $attendance->start);

        // 1. 出勤から退勤までの総労働時間（休憩含む）を秒で計算
        $workSeconds = $finishTime->diffInSeconds($startTime);

        // 2. 休憩時間の合計を計算
        $rests = Rest::where('attendance_id', $attendance->id)->get();
        $restSeconds = 0;

        foreach ($rests as $rest) {
            if ($rest->finish) {
                $restStart = Carbon::createFromFormat('H:i:s', $rest->start);
                $restFinish = Carbon::createFromFormat('H:i:s', $rest->finish);
                $restSeconds += $restFinish->diffInSeconds($restStart);
            }
        }

        // 3. 実働時間 = 総労働時間 - 休憩時間
        $actualWorkSeconds = max($workSeconds - $restSeconds, 0);

        // 4. 時:分:秒 形式に変換
        $hours   = floor($actualWorkSeconds / 3600);
        $minutes = floor(($actualWorkSeconds % 3600) / 60);
        $seconds = $actualWorkSeconds % 60;

        $fullTime = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

        // 5. 勤怠情報を更新
        $attendance->update([
            'status' => 2, // 退勤済み
            'finish' => $finishTime->format('H:i:s'),
            'full'   => $fullTime,
        ]);
    }

    return redirect('/attendance');
}
}
