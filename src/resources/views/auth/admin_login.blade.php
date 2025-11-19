{{--７ログイン画面（管理者）--}}

@extends('layouts/admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/admin_login.css')}}">
@endsection

@section('content')

<div>
    
    <div>

        <h1>管理者ログイン</h1>

        <form action="/admin/login" method="post">
            @csrf

            <div>
                <p>メールアドレス</p>
                <input type="text" name="email" value="{{ old('email') }}" />
                <div >
                    @error('email')
                    {{ $message }}
                    @enderror
                </div>
            </div>
            <div>
                <p>パスワード</p>
                <input type="password" name="password" />
                <div>
                    @error('password')
                    {{ $message }}
                    @enderror
                </div>
            </div>

            <button type="submit" >管理者ログインする</button>

        </form>

    </div>

</div>

@endsection