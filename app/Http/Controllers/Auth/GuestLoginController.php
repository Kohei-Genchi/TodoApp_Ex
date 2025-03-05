<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GuestLoginController extends Controller
{
    /**
     * Handle guest login request.
     * This should ONLY happen when the "かんたんログイン" button is clicked.
     */
    public function login(Request $request)
    {

        // Log that this method was called
        Log::info('Guest login requested through "かんたんログイン" button');

        // This is ONLY for "かんたんログイン" button clicks
        $user = User::where('email', 'guest@example.com')->first();

        if (!$user) {
            // Create guest user only when explicitly requested through the button
            Log::info('Creating guest user because "かんたんログイン" button was clicked');
            $user = User::create([
                'name' => 'Guest User',
                'email' => 'guest@example.com',
                'password' => bcrypt('guest-password')
            ]);
        }

        Auth::login($user);

        return redirect()->route('todos.index');
    }
}
