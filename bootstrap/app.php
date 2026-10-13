<?php

use App\Exceptions\InvalidStateTransitionException;
use App\Exceptions\SuratGenerationException;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureKaprodi;
use App\Http\Middleware\EnsureMahasiswa;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'mahasiswa' => EnsureMahasiswa::class,
            'admin' => EnsureAdmin::class,
            'kaprodi' => EnsureKaprodi::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 419 CSRF token expired — refresh token dan redirect ke halaman yang sama
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            // Regenerate token agar form berikutnya valid
            if ($request->hasSession()) {
                $request->session()->regenerateToken();
            }

            // Jika dari halaman login — redirect back ke login agar form fresh
            if ($request->routeIs('login') || $request->is('login')) {
                return redirect()->route('login')
                    ->with('error', 'Token keamanan kadaluarsa. Silakan coba login kembali.');
            }

            // Untuk halaman lain — redirect back dengan pesan
            return back()->with('error', 'Token keamanan kadaluarsa. Silakan coba lagi.');
        });
        // Tangani error file permission (rename/write pada storage) — jangan tampilkan ke user
        $exceptions->render(function (ErrorException $e, Request $request) {
            // Khusus error rename file cache view (Windows file locking)
            if (str_contains($e->getMessage(), 'rename(') && str_contains($e->getMessage(), 'Access is denied')) {
                // Log error tapi jangan crash halaman
                Log::warning('View cache write error (ignored): '.$e->getMessage());

                // Jika Livewire request, kembalikan response kosong agar tidak break UI
                if ($request->is('livewire/*') || $request->header('X-Livewire')) {
                    return response()->json(['effects' => [], 'serverMemo' => []], 200);
                }

                // Jika request biasa, redirect back dengan pesan agar user tahu
                return back()->with('warning', 'Gagal menulis cache tampilan (izin folder storage). Coba muat ulang halaman.');
            }
        });

        $exceptions->render(function (SuratGenerationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 500);
            }

            return back()->with('error', $e->getMessage());
        });

        $exceptions->render(function (InvalidStateTransitionException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        });
    })->create();
