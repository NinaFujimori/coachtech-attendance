{{-- ２ログイン画面（一般ユーザー） --}}

@extends('layouts/app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/login.css')}}">
@endsection

@section('content')

<div>
    
    <div>

        <h1>ログイン</h1>

        <form action="/login" method="post">
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

            <button type="submit" >ログインする</button><br>
            <a href="/register" >会員登録はこちら</a>

        </form>

    </div>

</div>

@endsection