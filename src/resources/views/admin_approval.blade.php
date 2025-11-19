{{-- １３修正申請承認画面（管理者） --}}

@extends('layouts/admin_app')

@section('css')
@endsection

@section('content')

<div>
    <div>
        <h1>勤怠詳細</h1>
    </div>

    <div>
        <form action="{{ route('admin.approve', ['id' => $attendance->id]) }}" method="POST">
            @csrf

            {{-- 新規登録時は date を hidden で送る --}}
            @if (!$attendance || !$attendance->id)
                <input type="hidden" name="date" value="{{ $date ?? '' }}">
            @endif

            <table>
                <tr>
                    <th>名前</th>
                    <td>{{ $user->name }}</td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td>{{ $year }}</td>
                    <td></td>
                    <td>{{ $monthDay }}</td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        <p>{{ old('start_time', $attendance?->start ? substr($attendance->start, 0, 5) : '') }}</p>
                        <input type="hidden" name="start_time"
                        value="{{ old('start_time', $attendance?->start ? substr($attendance->start, 0, 5) : '') }}"
                        {{ $isApproved ?  : '' }}>
                    </td>
                    <td>～</td>
                    <td>
                        <p>{{ old('finish_time', $attendance?->finish ? substr($attendance->finish, 0, 5) : '') }}</p>
                        <input type="hidden" name="finish_time"
                        value="{{ old('finish_time', $attendance?->finish ? substr($attendance->finish, 0, 5) : '') }}"
                        {{ $isApproved ?  : '' }}>
                    </td>
                </tr>

                @foreach ($rests as $index => $rest)
                    <tr class="{{ $rest->status == 1 ? 'rest-pending' : '' }}">
                        <th>休憩{{ $index === 0 ? '' : $index + 1 }}</th>
                        <td>
                            <p>{{ old("rests.$index.start", $rest->start ? substr($rest->start, 0, 5) : '') }}</p>
                            <input type="hidden" name="rests[{{ $index }}][start]"
                                value="{{ old("rests.$index.start", $rest->start ? substr($rest->start, 0, 5) : '') }}"
                                {{ $isApproved ? 'disabled' : '' }}>
                        </td>
                        <td>～</td>
                        <td>
                            <p>{{ old("rests.$index.finish", $rest->finish ? substr($rest->finish, 0, 5) : '') }}</p>
                            <input type="hidden" name="rests[{{ $index }}][finish]"
                                value="{{ old("rests.$index.finish", $rest->finish ? substr($rest->finish, 0, 5) : '') }}"
                                {{ $isApproved ? 'disabled' : '' }}>
                        </td>
                    </tr>
                @endforeach

                @if (!$isApproved)
                    @php
                        $newIndex = $rests->count();
                    @endphp
                    <tr>
                        <th>休憩{{ $newIndex === 0 ? '' : $newIndex + 1 }}</th>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endif


                <tr>
                    <th>備考</th>
                    <td>
                        <p>{{ old('description', $attendance?->description ?? '') }}</p>
                        <input type="hidden" name="description"
                        value="{{ old('description', $attendance?->description ?? '') }}">
                    </td>
                </tr>
            </table>

            @if (!$isApproved)
                <button type="submit">承認</button>
            @else
            <div>
                <p>承認済み</p>
            </div>
            @endif
        </form>

    </div>
</div>

@endsection