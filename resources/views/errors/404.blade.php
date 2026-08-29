<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 — Halaman Tidak Ditemukan</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased bg-slate-50">
    <div class="flex min-h-screen items-center justify-center px-4">
        <div class="max-w-sm w-full text-center space-y-6">
            {{-- Icon --}}
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-brand-100">
                <svg class="h-10 w-10 text-brand-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.3-4.3"/>
                    <path d="M11 8v3"/>
                    <path d="M11 14h.01"/>
                </svg>
            </div>

            {{-- Text --}}
            <div>
                <h1 class="text-6xl font-bold text-brand-500">404</h1>
                <h2 class="mt-2 text-xl font-semibold text-slate-800">Halaman Tidak Ditemukan</h2>
                <p class="mt-2 text-sm text-slate-500 leading-relaxed">
                    Halaman yang Anda cari tidak ada atau sudah dipindahkan.
                </p>
            </div>

            {{-- Button --}}
            <div class="flex justify-center gap-3">
                @auth
                    <a href="{{ route(auth()->user()->role . '.dashboard') }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 transition-colors">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                        Kembali ke Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 transition-colors">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" x2="3" y1="12" y2="12"/></svg>
                        Login
                    </a>
                @endauth
            </div>

            <p class="text-xs text-slate-400">E-Administrasi Prodi</p>
        </div>
    </div>
</body>
</html>
