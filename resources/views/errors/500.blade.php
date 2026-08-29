<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 — Kesalahan Server</title>
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="flex min-h-screen items-center justify-center px-4">
        <div class="max-w-md w-full text-center">
            <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-red-100 text-4xl">
                ⚠️
            </div>
            <h1 class="text-5xl font-bold text-red-500 mb-3">500</h1>
            <h2 class="text-xl font-semibold text-gray-800 mb-2">Terjadi Kesalahan Server</h2>
            <p class="text-gray-500 mb-6">
                Terjadi kesalahan pada server. Tim teknis sudah diberitahu. Silakan coba lagi beberapa saat.
            </p>
            <div class="flex justify-center gap-3">
                @auth
                    <a href="{{ route(auth()->user()->role . '.dashboard') }}"
                       class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-700">
                        ← Kembali ke Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-700">
                        Login
                    </a>
                @endauth
            </div>
            <p class="mt-6 text-xs text-gray-400">E-Administrasi Prodi</p>
        </div>
    </div>
</body>
</html>
