<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SADIK') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">

<div class="min-h-screen lg:grid lg:grid-cols-2">

    {{-- ===== KIRI: Branding ===== --}}
    <div class="hidden lg:flex flex-col justify-between bg-gradient-to-br from-brand-500 to-brand-700 px-12 py-16">
        {{-- Logo --}}
        <div class="flex items-center gap-4">
            <img src="{{ asset('images/logo.png') }}" alt="Logo Prodi" class="h-16 w-16 shrink-0 object-contain drop-shadow-md" />
            <div>
                <p class="text-sm font-bold leading-tight text-white">SISTEM ADMINISTRASI DIGITAL</p>
                <p class="text-sm font-bold leading-tight text-white">ILMU KOMUNIKASI</p>
            </div>
        </div>

        {{-- Teks tengah --}}
        <div class="space-y-5">
            <h2 class="text-3xl font-bold leading-snug text-white">
                Satu Akses,<br>Semua Beres.
            </h2>
            <p class="text-base text-brand-100 leading-relaxed">
                Portal Layanan Administrasi Digital Program Studi Ilmu Komunikasi, Fakultas Ilmu Sosial dan Ilmu Politik, Universitas Syiah Kuala.
            </p>

            <ul class="mt-6 space-y-3">
                @foreach ([
                    'Pengajuan online tanpa antre',
                    'Status pengajuan real-time',
                    'Download surat kapan saja',
                ] as $text)
                    <li class="flex items-center gap-3 text-sm text-brand-50">
                        <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-white/20">
                            <svg class="h-3 w-3 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m20 6-11 11-5-5"/></svg>
                        </div>
                        {{ $text }}
                    </li>
                @endforeach
            </ul>
        </div>

        <p class="text-xs text-brand-200">
            &copy; {{ date('Y') }} Sistem Administrasi Digital Ilmu Komunikasi — Universitas Syiah Kuala
        </p>
    </div>

    {{-- ===== KANAN: Form ===== --}}
    <div class="flex min-h-screen flex-col items-center justify-center bg-slate-50 px-6 py-12 lg:min-h-0">
        {{-- Logo mobile --}}
        <div class="mb-8 flex items-center gap-3 lg:hidden">
            <img src="{{ asset('images/logo.png') }}" alt="Logo Prodi" class="h-14 w-auto shrink-0 object-contain" />
            <div>
                <p class="text-sm font-bold leading-tight text-slate-800">SISTEM ADMINISTRASI DIGITAL</p>
                <p class="text-sm font-bold leading-tight text-slate-800">ILMU KOMUNIKASI</p>
            </div>
        </div>

        <div class="w-full max-w-sm">
            {{ $slot }}
        </div>
    </div>

</div>
</body>
</html>
