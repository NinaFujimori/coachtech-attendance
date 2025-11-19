{{-- ６勤怠詳細画面（一般ユーザー） --}}

@extends('layouts/app')

@section('css')
@endsection

@section('content')
<div>
    <div>
        <h1>勤怠詳細</h1>
    </div>

    <div>
        <form 
            action="{{ $attendance && $attendance->id 
                        ? route('attendance.fix', ['id' => $attendance->id]) 
                        : route('attendance.fix') }}" 
            method="POST">
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
                        <input type="time" name="start_time"
                        value="{{ old('start_time', $attendance?->start ? substr($attendance->start, 0, 5) : '') }}"
                        {{ $isApproved ? 'disabled' : '' }}>
                    </td>
                    <td>～</td>
                    <td>
                        <input type="time" name="finish_time"
                        value="{{ old('finish_time', $attendance?->finish ? substr($attendance->finish, 0, 5) : '') }}"
                        {{ $isApproved ? 'disabled' : '' }}>
                    </td>
                </tr>
                <tr>
                    <th></th>
                    <td>
                        @error('start_time')
                        {{ $message }}
                        @enderror
                    </td>
                    <td>
                        @error('finish_time')
                        {{ $message }}
                        @enderror
                    </td>
                </tr>

                @foreach ($rests as $index => $rest)
                    <tr class="{{ $rest->status == 1 ? 'rest-pending' : '' }}">
                        <th>休憩{{ $index === 0 ? '' : $index + 1 }}</th>
                        <td>
                            <input type="time" name="rests[{{ $index }}][start]"
                                value="{{ old("rests.$index.start", $rest->start ? substr($rest->start, 0, 5) : '') }}"
                                {{ $isApproved ? 'disabled' : '' }}>
                        </td>
                        <td>～</td>
                        <td>
                            <input type="time" name="rests[{{ $index }}][finish]"
                                value="{{ old("rests.$index.finish", $rest->finish ? substr($rest->finish, 0, 5) : '') }}"
                                {{ $isApproved ? 'disabled' : '' }}>
                        </td>
                    </tr>
                    <tr>
                        <th></th>
                        <td>
                            @error("rests.$index.start")
                            {{ $message }}
                            @enderror
                        </td>
                        <td>
                            @error("rests.$index.finish")
                            {{ $message }}
                            @enderror
                        </td>
                    </tr>
                    
                @endforeach

                @if (!$isApproved)
                    @php
                        $newIndex = $rests->count();
                    @endphp
                    <tr>
                        <th>休憩{{ $newIndex === 0 ? '' : $newIndex + 1 }}</th>
                        <td>
                            <input type="time" name="rests[{{ $newIndex }}][start]" value="">
                        </td>
                        <td>～</td>
                        <td>
                            <input type="time" name="rests[{{ $newIndex }}][finish]" value="">
                        </td>
                    </tr>
                    <tr>
                        <th></th>
                        <td>
                            @error("rests.$newIndex.start")
                            {{ $message }}
                            @enderror
                        </td>
                        <td>
                            @error("rests.$newIndex.finish")
                            {{ $message }}
                            @enderror
                        </td>
                    </tr>
                @endif


                <tr>
                    <th>備考</th>
                    <td>
                        <textarea name="description" {{ $isApproved ? 'disabled' : '' }}>{{ old('description', $attendance?->description ?? '') }}</textarea>
                    </td>
                </tr>
                <tr>
                    <th></th>
                    <td>
                        @error('description')
                            {{ $message }}
                        @enderror
                    </td>
                </tr>
            </table>

            @if (!$isApproved)
                <button type="submit">修正</button>
            @else
                <p>＊承認待ちのため修正はできません。</p>
            @endif
        </form>

    </div>
</div>
@endsection
