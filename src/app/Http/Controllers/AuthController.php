<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;

class AuthController extends Controller
{
    //　会員登録機能
    public function register(RegisterRequest $request)
    {
        // バリデーション（入力チェック）
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // ユーザーを作成
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'class' => 1,
        ]);

        // メールで認証とかに使う記述らしい。実装時詳細確認予定。
        event(new Registered($user));

        Auth::login($user);

        // 打刻画面に移動。
        return redirect('/attendance');
    }


    // ログイン機能
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        // 入力されたメールのユーザーを取得
        $user = User::where('email', $credentials['email'])->first();

        // ユーザーが存在しない
        if (!$user) {
            return back()->withErrors(['email' => 'ログイン情報が登録されていません']);
        }

        // 管理者が一般ログイン画面からログインしようとした場合
        if ($user->class === 0) {
            return back()->withErrors(['email' => '一般ユーザーのアカウントでログインしてください']);
        }

        // 通常の認証処理
        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password'], 'class' => 1])) {
            $request->session()->regenerate();
            return redirect()->intended('/attendance');
        }

        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout(); // 認証情報を破棄

        // セッションを無効化
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // ログイン画面に戻す
        return redirect('/login');
    }
}
