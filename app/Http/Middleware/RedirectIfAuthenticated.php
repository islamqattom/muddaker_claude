<?php
namespace App\Http\Middleware;
use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
class RedirectIfAuthenticated {
    public function handle(Request $request, Closure $next, string ...$guards): Response {
        $guards = empty($guards) ? [null] : $guards;
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::user();
                return match($user->role) {
                    'admin' => redirect()->route('admin.dashboard'),
                    'supervisor' => redirect()->route('supervisor.dashboard'),
                    'student' => redirect()->route('student.dashboard'),
                    'parent' => redirect()->route('parent.dashboard'),
                    default => redirect('/'),
                };
            }
        }
        return $next($request);
    }
}
