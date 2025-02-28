<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GuestLoginController extends Controller
{
    /**
     * ゲストユーザーとしてログイン
     */
    public function login(): RedirectResponse
    {
        // ゲストユーザーが存在するか確認
        $guestUser = User::where('email', 'guest@example.com')->first();

        // 存在しない場合は作成
        if (!$guestUser) {
            $guestUser = User::create([
                'name' => 'ゲストユーザー',
                'email' => 'guest@example.com',
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
            ]);
        }

        // ログイン処理
        Auth::login($guestUser);

        // セッションを再生成してセキュリティを高める
        request()->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
