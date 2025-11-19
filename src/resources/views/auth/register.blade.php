{{-- １会員登録画面（一般ユーザー） --}} 

@extends('layouts/app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/register.css')}}">
@endsection

@section('content')

<div class="register">

    <div class="register__inner">

        <h1>会員登録</h1>

        <form action="/register" method="post" class="register__form">
            @csrf

            <div>
                <p>名前</p>
                <input type="text" name="name" value="{{ old('name') }}" />
                <div>
                    @error('name')
                    {{ $message }}
                    @enderror
                </div>
            </div>
            <div>
                <p>メールアドレス</p>
                <input type="text" name="email" value="{{ old('email') }}" />
                <div>
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
                        @if($message !== 'パスワードと一致しません')
                            {{ $message }}
                        @endif
                    @enderror
                </div>
            </div>

            <div>
                <p>パスワード確認</p>
                <input type="password" name="password_confirmation" />
                <div>
                    @error('password')
                        @if($message === 'パスワードと一致しません')
                            {{ $message }}
                        @endif
                    @enderror
                </div>
            </div>

            <input type="hidden" name="class" value=1>
            
            <button type="submit" >登録する</button><br>
            <a href="/login">ログインはこちら</a>
        </form>
    </div>

</div>

@endsection