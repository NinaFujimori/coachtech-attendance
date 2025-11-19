{{-- ヘッダー表示画面（一般ユーザー）--}} 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css')}}">
    <link rel="stylesheet" href="{{ asset('css/common.css')}}">
    @yield('css')
</head>
<body>
    <div class="app">

        <header class="header">
            <div class="headr__inner">
                <div class="header__image">
                    <img src="storage/image/logo.svg" alt="COACHTECH">
                </div>
            
                @if(Auth::check())

                    <div>
                        @if(optional($todayAttendance)->status !== 2)
                            <a href="/attendance">勤怠</a>
                        @endif
                    </div>
                    <div>
                        @if(optional($todayAttendance)->status !== 2)
                            <a href="/attendance/list">勤怠一覧</a>
                        @else
                            <a href="/attendance/list">今月の出勤一覧</a>
                        @endif
                    </div>
                    <div>
                        @if(optional($todayAttendance)->status !== 2)
                            <a href="/attendance/request">申請</a>
                        @else
                            <a href="/attendance/request">申請一覧</a>
                        @endif
                    </div>
                    <div>
                        <form action="/logout" method="post">
                            @csrf
                            <button>ログアウト</button>
                        </form>
                    </div>
                @endif

            </div>
            
        </header>

        <div class="content">
            @yield('content')
        </div>

    </div>
    
</body>
</html>