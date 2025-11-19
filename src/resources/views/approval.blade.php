{{-- ５申請一覧画面（一般ユーザー） --}}

@extends('layouts/app')

@section('css')
@endsection

@section('content')
<div>
    <div>
        <h1>申請一覧</h1>
    </div>

    <div>
        <form action="{{ route('attendance.approved') }}" method="get" class="top__button">
            <button formaction="{{ route('attendance.request') }}">承認待ち</button>
            <button formaction="{{ route('attendance.approved') }}">承認済み</button>
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
                {{-- approvals または attendances のどちらかが存在する場合 --}}
                @php
                    $list = $approvals ?? $attendances ?? collect();
                @endphp

                @forelse($list as $item)
                    @php
                        // approvals から来た場合と attendances から来た場合で差を吸収
                        $attendance = $item instanceof \App\Models\Approval
                            ? $item->attendance
                            : $item;
                    @endphp

                    <tr>
                        <td>
                            @if ($attendance->approval === 1)
                                承認待ち
                            @elseif ($attendance->approval === 2)
                                承認済み
                            @endif
                        </td>
                        <td>{{ $attendance->user->name ?? '不明' }}</td>
                        <td>{{ \Carbon\Carbon::parse($attendance->date)->format('Y/m/d') }}</td>
                        <td>{{ $item->description ?? '-' }}</td>
                        <td>{{ $item->updated_at->format('Y/m/d') }}</td>
                        <td>
                            <a href="{{ route('attendance.detail', ['id' => $attendance->id]) }}">詳細</a>
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

