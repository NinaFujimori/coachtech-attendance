{{-- ４勤怠一覧画面（一般ユーザー） --}}

@extends('layouts/app')

@section('css')
@endsection

@section('content')
<div>
    <div>
        <h1>勤務一覧</h1>
    </div>

    {{-- 月切り替え --}}
    <div>
        <a href="{{ route('list', ['month' => \Carbon\Carbon::parse($currentMonth)->subMonth()->format('Y-m')]) }}">←前月</a>
        <span>{{ \Carbon\Carbon::parse($currentMonth)->format('Y年n月') }}</span>
        <a href="{{ route('list', ['month' => \Carbon\Carbon::parse($currentMonth)->addMonth()->format('Y-m')]) }}">翌月→</a>
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
                        {{-- データがある場合は id で --}}
                        <a href="{{ route('attendance.detail', ['id' => $attendance['id']]) }}">詳細</a>
                    @else
                        {{-- データがない場合は date をクエリで --}}
                        <a href="{{ route('attendance.detail') }}?date={{ $attendance['date_raw'] }}">詳細</a>
                    @endif
                </td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection