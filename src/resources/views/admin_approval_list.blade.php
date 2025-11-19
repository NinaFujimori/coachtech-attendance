{{--１２申請一覧画面（管理者）--}} 

@extends('layouts/admin_app')

@section('css')
@endsection

@section('content')

<div>
    <div>
        <h1>申請一覧</h1>
    </div>

    <div>
        <form action="{{ route('attendance.approved') }}" method="get" class="top__button">
            <button formaction="{{ route('admin.requests') }}">承認待ち</button>
            <button formaction="{{ route('admin.approved') }}">承認済み</button>
        </form>
    </div>

    <div>
        <table>
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>

                @forelse($approvals as $approval)

                    <tr>
                        <td>
                            @if ($approval->approval === 1)
                                承認待ち
                            @elseif ($approval->approval === 2)
                                承認済み
                            @endif
                        </td>
                        <td>{{ $approval->attendance->user->name ?? '不明' }}</td>
                        <td>{{ $approval->attendance->date ? \Carbon\Carbon::parse($approval->attendance->date)->format('Y/m/d') : '-' }}</td>
                        <td>{{ $approval->description ?? '-' }}</td>
                        <td>{{ $approval->created_at->format('Y/m/d') }}</td>
                        <td>
                            <a href="{{ route('show.approval', ['id' => $approval->attendance_id]) }}">詳細</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">該当する申請はありません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection