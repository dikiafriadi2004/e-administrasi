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
        // Tangani error file permission (rename/write pada storage) — jangan tampilkan ke user
        $exceptions->render(function (\ErrorException $e, Request $request) {
            // Khusus error rename file cache view (Windows file locking)
            if (str_contains($e->getMessage(), 'rename(') && str_contains($e->getMessage(), 'Access is denied')) {
                // Log error tapi jangan crash halaman
                \Illuminate\Support\Facades\Log::warning('View cache write error (ignored): '.$e->getMessage());

                // Jika Livewire request, kembalikan response kosong agar tidak break UI
                if ($request->is('livewire/*') || $request->header('X-Livewire')) {
                    return response()->json(['effects' => [], 'serverMemo' => []], 200);
                }

                // Jika request biasa, redirect back
                return back();
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
