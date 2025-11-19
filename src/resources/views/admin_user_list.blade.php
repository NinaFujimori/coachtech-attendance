{{-- １０スタッフ一覧画面（管理者） --}}

@extends('layouts/admin_app')

@section('css')
@endsection

@section('content')

<div>
    <div>
        <h1>スタッフ一覧</h1>
    </div>

    <div>
        <table>
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
            @foreach($users as $user)
            <tr>
                <td>{{ $user['name'] }}</td>
                <td>{{ $user['email'] }}</td>
                <td>
                    <a href="{{ route('admin.user.detail', ['user_id' => $user->id]) }}">詳細</a>
                </td>
            </tr>
            @endforeach
        </table>
    </div>
</div>

@endsection