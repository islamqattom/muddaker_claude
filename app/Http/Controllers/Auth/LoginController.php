<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ], [
            'login.required' => 'يرجى إدخال اسم المستخدم أو البريد الإلكتروني أو رقم الهاتف',
            'password.required' => 'يرجى إدخال كلمة المرور',
        ]);

        $loginField = $this->determineLoginField($request->login);
        $credentials = [$loginField => $request->login, 'password' => $request->password];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            if (!$user->is_active) {
                // Invalidate the remember token to prevent re-login via remember cookie
                $user->remember_token = null;
                $user->save();
                Auth::logout();
                return back()->withErrors(['login' => 'حسابك موقوف. يرجى التواصل مع الإدارة.']);
            }

            return $this->redirectBasedOnRole($user);
        }

        return back()->withErrors([
            'login' => 'بيانات الدخول غير صحيحة.',
        ])->onlyInput('login');
    }

    private function redirectBasedOnRole($user)
    {
        return match($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'supervisor' => redirect()->route('supervisor.dashboard'),
            'student' => redirect()->route('student.dashboard'),
            'parent' => redirect()->route('parent.dashboard'),
            default => redirect('/'),
        };
    }

    private function determineLoginField(string $input): string
    {
        // Check if username exists in users table FIRST
        // This prevents usernames that look like emails from being misidentified
        if (User::where('username', $input)->exists()) {
            return 'username';
        }

        // Check if it's a valid email
        if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }

        // Otherwise, treat as phone
        return 'phone';
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
