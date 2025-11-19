<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AttendanceRequest;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\Approval;

class AdminApprovalController extends Controller
{
    // 申請一覧画面を表示
    public function adminRequest(){

        $userIds = User::where('class', 1)->pluck('id');

        // Approvalと関連するAttendance・Userを同時取得
        $approvals = Approval::whereHas('attendance', function ($query) use ($userIds) {
                $query->whereIn('user_id', $userIds)
                      ->where('approval', 1); // 勤怠のapproval=1（承認待ち）
            })
            ->with(['attendance.user']) // user と attendance を同時ロード
            ->orderByDesc('created_at')
            ->get();

        return view('admin_approval_list', compact('approvals'));
    } 

    // 承認済み申請一覧を表示
    public function adminApproved(Request $request){

        $userIds = User::where('class', 1)->pluck('id');

        $approvals = Approval::whereHas('attendance', function ($query) use ($userIds) {
                $query->whereIn('user_id', $userIds)
                      ->where('approval', 2); // 承認済み
            })
            ->with(['attendance.user'])
            ->orderByDesc('created_at')
            ->get();

        return view('admin_approval_list', compact('approvals'));
    } 

    // 修正申請承認画面を表示
    public function showApproval(Request $request, $id = null){
        $attendance = Attendance::with('rests')->find($id);

        if (!$attendance) {
            abort(404, '指定された勤怠データが見つかりません。');
        }

        // ユーザー取得
        $user = User::find($attendance->user_id);

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

        $date = $attendance->date;
        $year = \Carbon\Carbon::parse($date)->format('Y年');
        $monthDay = \Carbon\Carbon::parse($date)->format('m月d日');

        // approval（申請中 or 承認済み）を取得
        $approval = Approval::where('attendance_id', $attendance->id)
            ->orderByDesc('id')
            ->first();

        // --- 承認待ち判定 ---
        $isApproved = ($approval && $approval->approval == 2) ? true : false;

        return view('admin_approval', compact('attendance', 'rests', 'user', 'year', 'monthDay', 'isApproved', 'date'));
    }

    // 修正申請承認機能
    public function adminApprove($id){

        // attendancesテーブルのapprovalカラムを１→２に変更、approvalsテーブルのapprovalカラムが１のデータを反映

        // approvalsテーブルのapprovalカラムを１→２に変更

        //　restsテーブルの０のデータを削除、restsテーブルのapprovalカラムを１→２に変更

        // ① 対象の勤怠を取得
        $attendance = Attendance::findOrFail($id);

        // ② 該当の申請（承認待ち）データを取得
        $approval = Approval::where('attendance_id', $attendance->id)
            ->where('approval', 1)
            ->first();

        if (!$approval) {
            return redirect()->back()->with('error', '承認待ちの申請が見つかりません。');
        }

        // ③ 申請内容を勤怠に反映
        $attendance->update([
            'start'       => $approval->start,
            'finish'      => $approval->finish,
            'description' => $approval->description,
            'approval'    => 2, // 承認済みに更新
        ]);

        // ④ approvals テーブルの該当データを承認済みに
        $approval->update([
            'approval' => 2,
        ]);

        // ⑤ rests テーブル更新
        // （1）通常データ（approval=0）を削除
        Rest::where('attendance_id', $attendance->id)
            ->where('approval', 0)
            ->delete();

        // （2）申請中の休憩データ（approval=1）を承認済みに更新
        Rest::where('attendance_id', $attendance->id)
            ->where('approval', 1)
            ->update(['approval' => 2]);

        return redirect()
        ->route('show.approval', ['id' => $attendance->id]);

    } 
}
