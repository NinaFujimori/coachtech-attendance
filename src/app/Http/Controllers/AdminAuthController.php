<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;

class AdminAuthController extends Controller
{
    public function showAdmin()
    {
        return view('auth.admin_login');
    }

    public function adminLogin(LoginRequest $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // 入力されたメールのユーザーを取得
        $user = User::where('email', $credentials['email'])->first();

        // ユーザーが存在しない
        if (!$user) {
            return back()->withErrors(['email' => 'ログイン情報が登録されていません']);
        }

        // 一般ユーザーが管理者ログイン画面からログインしようとした場合
        if ($user->class === 1) {
            return back()->withErrors(['email' => '管理者のアカウントでログインしてください']);
        }

        // 通常の認証処理
        if (Auth::guard('admin')->attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'class' => 0
        ])) {
            $request->session()->regenerate();
            return redirect()->route('admin.attendance');
        }

        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }

    public function adminLogout(Request $request)
    {
        Auth::guard('admin')->logout(); // 認証情報を破棄

        // セッションを無効化
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // ログイン画面に戻す
        return redirect()->route('admin.login');
    }
}
