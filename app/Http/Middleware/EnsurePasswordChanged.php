<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->role === 'mahasiswa' && is_null($user->password_changed_at)) {
            if ($request->routeIs('mahasiswa.profil.show') ||
                $request->routeIs('password.update') ||
                $request->routeIs('logout')) {
                return $next($request);
            }

            return redirect()->route('mahasiswa.profil.show')
                ->with('error', 'Anda wajib mengganti password sebelum menggunakan aplikasi. Password default Anda adalah NIM.');
        }

        return $next($request);
    }
}
