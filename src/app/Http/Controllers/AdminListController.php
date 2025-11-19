<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Http\Requests\AttendanceRequest;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\Approval;

class AdminListController extends Controller
{
    public function adminList(Request $request)
    {
        $day = $request->input('day', Carbon::today()->toDateString()); // 'Y-m-d'

        // 一般ユーザー（classが1）を取得
        $users = User::where('class', 1)->get();

        $attendances = [];

        foreach ($users as $user) {
            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('date', $day)
                ->with('rests')
                ->first();

            // 勤怠データがあるユーザーのみ表示
            if ($attendance) {
                // 出退勤
                $start = $attendance->start ? Carbon::parse($attendance->start)->format('H:i') : '';
                $finish = $attendance->finish ? Carbon::parse($attendance->finish)->format('H:i') : '';

                // 休憩（approval == 0 のみ合算）
                $totalRestMinutes = 0;
                $approved0Rests = $attendance->rests->where('approval', 0);
                foreach ($approved0Rests as $rest) {
                    if ($rest->start && $rest->finish) {
                        $totalRestMinutes += Carbon::parse($rest->finish)->diffInMinutes(Carbon::parse($rest->start));
                    }
                }

                // 勤務時間合計
                $totalWorkMinutes = 0;
                if ($attendance->start && $attendance->finish) {
                    $workMinutes = Carbon::parse($attendance->finish)->diffInMinutes(Carbon::parse($attendance->start));
                    $totalWorkMinutes = max($workMinutes - $totalRestMinutes, 0);
                }

                $rest = sprintf('%02d:%02d', floor($totalRestMinutes / 60), $totalRestMinutes % 60);
                $full = sprintf('%02d:%02d', floor($totalWorkMinutes / 60), $totalWorkMinutes % 60);

                $attendances[] = [
                    'id' => $attendance->id,
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'date' => $day,
                    'start' => $start,
                    'finish' => $finish,
                    'rest' => $rest,
                    'full' => $full,
                ];
            }
        }

        return view('admin_list', [
            'attendances' => $attendances,
            'currentDay' => $day,
        ]);
    }


    public function adminDetail(Request $request, $id = null){
        // 初期化
        $attendance = null;
        $rests = collect([]);
        $approval = null;
        $date = $request->input('date') ?? null;
        $user = null;

        // まずは id が与えられている場合（リンクから）
        if ($id) {
            $attendance = Attendance::with('rests')->find($id);
        } elseif ($request->has('date') && $request->has('user_id')) {
            // id なければ date + user_id で探す（admin_list からクエリで来る想定）
            $date = $request->input('date');
            $userId = $request->input('user_id');
            $attendance = Attendance::where('user_id', $userId)
                ->whereDate('date', $date)
                ->with('rests')
                ->first();
        } elseif ($request->has('user_id')) {
            // user_id だけ来ている場合はその日のレコードがなくても user は取得しておく
            $userId = $request->input('user_id');
            $user = User::find($userId);
        }

        // もし attendance が取得できたら user を確定
        if ($attendance) {
            // attendance に user リレーションがあれば使う、なければ find
            if (isset($attendance->user)) {
                $user = $attendance->user;
            } elseif (!isset($user) && $attendance->user_id) {
                $user = User::find($attendance->user_id);
            }

            // approval（申請中データ）を取得
            $approval = Approval::where('attendance_id', $attendance->id)
                ->where('approval', 1)
                ->orderBy('id')
                ->first();

            // approval があるときは表示上上書き
            if ($approval) {
                $attendance->start = $approval->start ?? $attendance->start;
                $attendance->finish = $approval->finish ?? $attendance->finish;
                $attendance->description = $approval->description ?? $attendance->description;
            }

            // 休憩データ（申請中なら approval=1 のもの、それ以外は approval=0）
            $rests = Rest::where('attendance_id', $attendance->id)
                ->where('approval', $approval ? 1 : 0)
                ->orderBy('id')
                ->get();

            // 表示用日付
            $date = $attendance->date;
        }

        // user が未定義ならリクエストから取ってみる
        if (!$user && $request->has('user_id')) {
            $user = User::find($request->input('user_id'));
        }

        // --- 表示用の年月日整形 ---
        $year = isset($date) ? \Carbon\Carbon::parse($date)->format('Y年') : '';
        $monthDay = isset($date) ? \Carbon\Carbon::parse($date)->format('m月d日') : '';

        // --- 承認待ち判定 ---
        $isApproved = ($attendance && $approval) ? true : false;

        return view('admin_detail', compact('attendance', 'rests', 'user', 'year', 'monthDay', 'isApproved', 'date'));
    }

    public function adminFix(AttendanceRequest $request){
        // 対象ユーザー取得（もし不要なら削除）
        $user = User::find($request->user_id);

        // 勤怠データ取得
        $attendance = Attendance::find($request->id);

        if (!$attendance) {
            return back()->withErrors(['attendance' => '該当の勤怠データが見つかりません。']);
        }

        // 出退勤時間を更新
        $attendance->start = $request->start_time;
        $attendance->finish = $request->finish_time;
        $attendance->description = $request->description;

        // --- 実働時間(full)の計算 ---
        if ($request->start_time && $request->finish_time) {
            $startTime = Carbon::createFromFormat('H:i', $request->start_time);
            $finishTime = Carbon::createFromFormat('H:i', $request->finish_time);

            // 出勤〜退勤の総秒数
            $workSeconds = $finishTime->diffInSeconds($startTime);

            // 登録済みの休憩時間を取得
            $rests = Rest::where('attendance_id', $attendance->id)->get();
            $restSeconds = 0;

            foreach ($rests as $rest) {
                if ($rest->start && $rest->finish) {
                    $restStart = Carbon::createFromFormat('H:i:s', $rest->start);
                    $restFinish = Carbon::createFromFormat('H:i:s', $rest->finish);
                    $restSeconds += $restFinish->diffInSeconds($restStart);
                }
            }

            // 実働時間 = 総労働時間 - 休憩時間
            $actualWorkSeconds = max($workSeconds - $restSeconds, 0);

            // 秒 → "H:i:s" 形式で保存
            $attendance->full = gmdate('H:i:s', $actualWorkSeconds);
        }

        $attendance->save();

        // --- 休憩データ更新 ---
        if ($request->has('rests')) {
            // 既存の休憩データをすべて取得して index に対応付け
            $existingRests = Rest::where('attendance_id', $attendance->id)
                ->orderBy('id')
                ->get()
                ->values(); // index順にアクセスできるように
            
            $existingIds = $existingRests->pluck('id')->toArray();
            $usedIds = [];

            foreach ($request->rests as $index => $restInput) {
                $start = $restInput['start'] ?? null;
                $finish = $restInput['finish'] ?? null;

                // どちらも空ならスキップ
                if (!$start && !$finish) {
                    continue;
                }

                if ($rest->id) {
                    $usedIds[] = $rest->id;
                }

                // 既存があれば更新、無ければ新規作成
                $rest = $existingRests->get($index) ?? new Rest();
                $rest->attendance_id = $attendance->id;
                $rest->start = $start;
                $rest->finish = $finish;
                $rest->approval = 0; // 管理者が直接修正なら承認済みにしても良い
                $rest->save();
            }

            // 不要な休憩データを削除
            Rest::where('attendance_id', $attendance->id)
                ->whereNotIn('id', $usedIds)
                ->delete();
        }


        return redirect()
            ->route('admin.detail', ['id' => $attendance->id]);
    }

    public function staff(){
        $users = User::all()
            ->where('class', 1);

        return view('admin_user_list',compact('users'));
    }

    public function checkStaff(Request $request, $user_id)
    {
        // user を取得して存在チェック
        $user = User::find($user_id);
        if (! $user) {
            abort(404, 'User not found');
        }

        // 現在の月（指定がなければ今月）
        $currentMonth = $request->input('month', Carbon::now()->format('Y-m'));
        $startOfMonth = Carbon::parse($currentMonth)->startOfMonth();
        $endOfMonth = Carbon::parse($currentMonth)->endOfMonth();

        // 勤怠データ取得（キーを "Y-m-d" に）
        $attendanceRecords = Attendance::where('user_id', $user_id)
            ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->with('rests')
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->date)->format('Y-m-d');
            });


        // 月の日付をすべてループ
        $attendances = [];
        $date = $startOfMonth->copy();

        while ($date->lte($endOfMonth)) {
            $key = $date->format('Y-m-d');

            if (isset($attendanceRecords[$key])) {
                // 勤怠データがある場合
                $attendance = $attendanceRecords[$key];
                $start = $attendance->start ? Carbon::parse($attendance->start)->format('H:i') : '';
                $finish = $attendance->finish ? Carbon::parse($attendance->finish)->format('H:i') : '';

                // 休憩時間合計（approval=0 のものを合算）
                $totalRestMinutes = 0;
                foreach ($attendance->rests as $rest) {
                    if ($rest->approval == 0 && $rest->start && $rest->finish) {
                        $totalRestMinutes += Carbon::parse($rest->finish)->diffInMinutes(Carbon::parse($rest->start));
                    }
                }

                // 勤務時間合計
                $totalWorkMinutes = 0;
                if ($attendance->start && $attendance->finish) {
                    $workMinutes = Carbon::parse($attendance->finish)->diffInMinutes(Carbon::parse($attendance->start));
                    $totalWorkMinutes = max($workMinutes - $totalRestMinutes, 0);
                }

                $rest = sprintf('%02d:%02d', floor($totalRestMinutes / 60), $totalRestMinutes % 60);
                $full = sprintf('%02d:%02d', floor($totalWorkMinutes / 60), $totalWorkMinutes % 60);

                $id = $attendance->id;
                $date_raw = $key; // "Y-m-d"
            } else {
                // データがない場合
                $start = '';
                $finish = '';
                $rest = '';
                $full = '';
                $id = null;
                $date_raw = $key;
            }

            // 日付表示 (例: 09/01(月))
            $formattedDate = $date->format('m/d') . '(' . ['日', '月', '火', '水', '木', '金', '土'][$date->dayOfWeek] . ')';

            $attendances[] = [
                'id' => $id,
                'user_id' => $user->id,   // ← ここを必ず入れる（ビューの詳細リンクで使う）
                'date_raw' => $date_raw,  // 管理詳細に渡すなら Y-m-d を使うこと
                'date' => $formattedDate,
                'start' => $start,
                'finish' => $finish,
                'rest' => $rest,
                'full' => $full,
            ];

            $date->addDay();
        }

        return view('admin_list_by_user', compact('user', 'attendances', 'currentMonth'));
    }

}
