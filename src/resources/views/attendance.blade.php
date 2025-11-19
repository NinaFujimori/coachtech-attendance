{{-- ３出勤登録画面（一般ユーザー） --}}

@extends('layouts/app')

@section('css')
@endsection

@section('content')

<div>
    
    <div>

        <div>
            @if (is_null($attendance))
                <p>勤務外</p>
            @elseif ($attendance->status === 0)
                <p>出勤中</p>
            @elseif ($attendance->status === 1)
                <p>休憩中</p>
            @elseif ($attendance->status === 2)
                <p>退勤済</p>
            @else
                <p>エラー</p>
            @endif
        </div>

        <h2 id="date">{{ $date }}</h2>
        <h1 id="clock">{{ $time }}</h1>

        <div>
            @if (is_null($attendance))
                {{-- 勤怠データなし → 出勤前 --}}
                <form action="{{ url('/attendance/at_work') }}" method="POST">
                    @csrf
                    <button>出勤</button>
                </form>

            @elseif ($attendance->status === 0)
                {{-- 出勤中 --}}
                <form action="{{ url('/attendance/leave_work') }}" method="POST">
                    @csrf
                    <button>退勤</button>
                </form>
                <form action="{{ url('/attendance/at_rest') }}" method="POST">
                    @csrf
                    <button>休憩入</button>
                </form>

            @elseif ($attendance->status === 1)
                {{-- 休憩中 --}}
                <form action="{{ url('/attendance/leave_rest') }}" method="POST">
                    @csrf
                    <button>休憩戻</button>
                </form>

            @elseif ($attendance->status === 2)
                {{-- 退勤済 --}}
                <p>お疲れ様でした。</p>
            @endif
        </div>


    </div>

</div>

<script>
    function updateClock() {
        const now = new Date();

        // 曜日を日本語で表示
        const week = ['日', '月', '火', '水', '木', '金', '土'];
        const y = now.getFullYear();
        const m = String(now.getMonth() + 1).padStart(2, '0');
        const d = String(now.getDate()).padStart(2, '0');
        const w = week[now.getDay()];

        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');

        // 日付と時間を更新
        document.getElementById('date').textContent = `${y}年${m}月${d}日（${w}）`;
        document.getElementById('clock').textContent = `${hours}:${minutes}`;
    }

    // 1秒ごとに時刻更新
    setInterval(updateClock, 1000);

    // ページ読み込み時にも即実行
    updateClock();
</script>

@endsection