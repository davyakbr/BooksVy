<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsCustomer
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('warning', 'Silakan masuk (login) ke akun Anda terlebih dahulu.');
        }

        if (Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard')->with('info', 'Anda dialihkan ke panel Administrator.');
        }

        return $next($request);
    }
}
