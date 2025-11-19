{{-- ヘッダー表示画面（管理者）--}} 

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
                        <a href="/admin/attendance">勤怠一覧</a>
                    </div>
                    <div>
                        <a href="/admin/users">スタッフ一覧</a>
                    </div>
                    <div>
                        <a href="/admin/requests">申請一覧</a>
                    </div>
                    <div>
                        <form action="/admin/logout" method="post">
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