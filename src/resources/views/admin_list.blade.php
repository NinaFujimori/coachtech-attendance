{{-- ８勤怠一覧画面（管理者） --}}

@extends('layouts/admin_app')

@section('css')
@endsection

@section('content')
<div>
    <div>
        <h1>{{ \Carbon\Carbon::parse($currentDay)->format('Y年n月j日') }}の勤怠</h1>
    </div>

    {{-- 日付切り替え --}}
    <div>
        <a href="{{ route('admin.attendance', ['day' => \Carbon\Carbon::parse($currentDay)->subDay()->toDateString()]) }}">←前日</a>
        <span>{{ \Carbon\Carbon::parse($currentDay)->format('Y/n/j') }}</span>
        <a href="{{ route('admin.attendance', ['day' => \Carbon\Carbon::parse($currentDay)->addDay()->toDateString()]) }}">翌日→</a>
    </div>

    <div>
        <table>
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
            @foreach($attendances as $attendance)
            <tr>
                <td>{{ $attendance['name'] }}</td>
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
    </div>
</div>
@endsection