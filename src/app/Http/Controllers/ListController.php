<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AttendanceRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\Approval;

class ListController extends Controller
{
    public function list(Request $request){
        $userId = Auth::id();

        // 現在の月（指定がなければ今月）
        $currentMonth = $request->input('month', Carbon::now()->format('Y-m'));
        $startOfMonth = Carbon::parse($currentMonth)->startOfMonth();
        $endOfMonth = Carbon::parse($currentMonth)->endOfMonth();

        // 勤怠データ取得
        $attendanceRecords = Attendance::where('user_id', $userId)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->with('rests')
            ->get()
            ->keyBy(function ($item) {
                // "Y-m-d" 形式の日付をキーにする
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

                // 休憩時間合計
                $totalRestMinutes = 0;
                $rests = $attendance->rests->where('approval', 0);
                foreach ($attendance->rests as $rest) {
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

                $id = $attendance->id;
            } else {
                // データがない場合
                $start = '';
                $finish = '';
                $rest = '';
                $full = '';
                $id = null;
            }

            // 日付表示 (例: 09/01(月))
            $formattedDate = $date->format('m/d') . '(' . ['日', '月', '火', '水', '木', '金', '土'][$date->dayOfWeek] . ')';

            $attendances[] = [
                'id' => $id,
                'date_raw' => $key, // ← 生の日付（Y-m-d）を追加
                'date' => $formattedDate,
                'start' => $start,
                'finish' => $finish,
                'rest' => $rest,
                'full' => $full,
            ];

            $date->addDay();
        }

        return view('list', compact('attendances', 'currentMonth'));
    }

    public function detail(Request $request, $id = null)
    {
        $user = Auth::user();
        $userId = $user->id;

        // --- 勤怠データを取得 ---
        if ($id) {
            $attendance = Attendance::where('id', $id)
                ->where('user_id', $userId)
                ->first();
        } elseif ($request->has('date')) {
            $date = $request->input('date');
            $attendance = Attendance::where('user_id', $userId)
                ->whereDate('date', $date)
                ->first();
        } else {
            $attendance = null;
        }

        // --- 休憩データの初期化 ---
        $rests = collect([]);

        if ($attendance) {

            // --- approvalテーブルのapprovalカラムが１のデータを取得 ---
            $approval = Approval::where('attendance_id', $attendance->id)
                ->where('approval', 1)
                ->orderBy('id')
                ->first();

            // --- 表示用データを準備 ---
            if ($approval) {
                // 申請中：Approval の内容を仮上書き表示
                $attendance->start = $approval->start ?? $attendance->start;
                $attendance->finish = $approval->finish ?? $attendance->finish;
                $attendance->description = $approval->description ?? $attendance->description;
            }

            // --- 休憩データを取得 ---
            // 申請中のときは status=1 の休憩のみ、それ以外は確定済み
            if ($approval) {
                $rests = Rest::where('attendance_id', $attendance->id)
                    ->where('approval', 1)
                    ->orderBy('id')
                    ->get();
            } else {
                $rests = Rest::where('attendance_id', $attendance->id)
                    ->where('approval', 0)
                    ->orderBy('id')
                    ->get();
            }

            $date = $attendance->date;
        }

        // --- 表示用の年月日整形 ---
        $year = isset($date) ? \Carbon\Carbon::parse($date)->format('Y年') : '';
        $monthDay = isset($date) ? \Carbon\Carbon::parse($date)->format('m月d日') : '';

        // --- 承認待ち判定 ---
        $isApproved = $attendance && $approval;

        return view('detail', compact('attendance', 'rests', 'user', 'year', 'monthDay', 'isApproved', 'date'));
    }


    public function fix(AttendanceRequest $request, $id = null)
    {
        $user = Auth::user();

        // --- 勤怠データ取得 or 新規生成 ---
        $attendance = $id
            ? Attendance::findOrFail($id)
            : Attendance::firstOrNew([
                'user_id' => $user->id,
                'date' => $request->date,
            ]);

        $validated = $request->validated();

        // --- まだ存在しない勤怠なら仮作成（空データ） ---
        if (!$attendance->exists) {
            $attendance->start = null;
            $attendance->finish = null;
            $attendance->description = null;
            $attendance->approval = 0;
            $attendance->status = 3;
            $attendance->save(); // ←ここでID確定
        }

        // --- 承認中 or ロック状態なら編集不可 ---
        if ($attendance->approval == 1) {
            return back()->with('error', '承認待ちのため修正できません。');
        }

        // --- Approval に保存（新規または更新） ---
        $approval = \App\Models\Approval::updateOrCreate(
            ['attendance_id' => $attendance->id],
            [
                'approval' => 1,
                'start' => $validated['start_time'],
                'finish' => $validated['finish_time'],
                'description' => $validated['description'],
            ]
        );

        // --- 休憩データ処理 ---
        $existingRests = \App\Models\Rest::where('attendance_id', $attendance->id)->get()->values();

        // 既存休憩はすべて approval=1 に更新
        foreach ($existingRests as $r) {
            $r->update(['approval' => 1]);
        }

        // 入力された休憩データを登録 or 更新
        foreach ($validated['rests'] ?? [] as $index => $rest) {
            $start = $rest['start'] ?? null;
            $finish = $rest['finish'] ?? null;

            if (!empty($start) || !empty($finish)) {
                if (isset($existingRests[$index])) {
                    $existingRests[$index]->update([
                        'start' => $start,
                        'finish' => $finish,
                        'approval' => 1,
                    ]);
                } else {
                    \App\Models\Rest::create([
                        'attendance_id' => $attendance->id,
                        'start' => $start,
                        'finish' => $finish,
                        'approval' => 1,
                    ]);
                }
            }
        }

        // --- 状態更新 ---
        $attendance->approval = 1;
        $attendance->status = 1;
        $attendance->save();

        return redirect()
            ->route('attendance.detail', ['id' => $attendance->id]);
    }



    public function request()
    {
        $userId = Auth::id();

        $approvals = Approval::whereHas('attendance', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->where('approval', 1); // 勤怠側の approval 状態が 1（承認待ち）など
            })
            ->with(['attendance.user']) // 関連データもまとめて取得
            ->orderByDesc('created_at')
            ->get();

        return view('approval', compact('approvals'));
    }

    public function approved(Request $request){
        $userId = Auth::id();

        //　Approvalsテーブルにあるデータの内、attendance_idと繋がる勤怠データのuser_idが$userIdに該当するもの

        $attendances = Approval::where('approval', 2)
            ->whereHas('attendance', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->get();

        return view('approval', compact('attendances'));
    }
}
