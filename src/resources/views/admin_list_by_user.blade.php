{{-- １１スタッフ別勤怠一覧画面（管理者） --}}

@extends('layouts/admin_app')

@section('css')
@endsection

@section('content')

<div>
    <div>
        <h1>{{ $user->name }}さんの勤怠</h1>
    </div>

    {{-- 月切り替え --}}
    <div>
        <a href="{{ route('admin.user.detail', [
            'user_id' => $user->id,
            'month' => \Carbon\Carbon::parse($currentMonth)->subMonth()->format('Y-m')
        ]) }}">←前月</a>

        <span>{{ \Carbon\Carbon::parse($currentMonth)->format('Y年n月') }}</span>

        <a href="{{ route('admin.user.detail', [
            'user_id' => $user->id,
            'month' => \Carbon\Carbon::parse($currentMonth)->addMonth()->format('Y-m')
        ]) }}">翌月→</a>
    </div>

    <div>
        <table>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
            @foreach($attendances as $attendance)
            <tr>
                <td>{{ $attendance['date'] }}</td>
                <td>{{ $attendance['start'] }}</td>
                <td>{{ $attendance['finish'] }}</td>
                <td>{{ $attendance['rest'] }}</td>
                <td>{{ $attendance['full'] }}</td>
                <td>
                    @if ($attendance['id'])
                        <a href="{{ route('admin.detail', ['id' => $attendance['id'], 'user_id' => $attendance['user_id']]) }}">詳細</a>
                    @else
                        <a href="{{ route('admin.detail', ['user_id' => $attendance['user_id'], 'date' => $attendance['date']]) }}">詳細</a>
                    @endif
                </td>
            </tr>
            @endforeach
        </table>
        <div>
            <p>CSV出力</p>
        </div>
    </div>
</div>
@endsection