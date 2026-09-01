<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} — {{ config('app.name', 'E-Administrasi') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-900">

<div class="flex h-screen overflow-hidden">

    {{-- ===================== SIDEBAR ===================== --}}
    <aside id="sidebar"
           class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-white border-r border-slate-200 shadow-sm transition-transform duration-300 lg:static lg:translate-x-0 -translate-x-full">

        {{-- Logo --}}
        <div class="flex h-16 shrink-0 items-center gap-3 border-b border-slate-100 px-5">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-500">
                <x-icon name="graduation-cap" class="h-5 w-5 text-white" />
            </div>
            <div class="min-w-0">
                <p class="truncate text-sm font-bold text-slate-800">E-Administrasi</p>
                <p class="truncate text-xs text-slate-400">Portal Akademik</p>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">

            @php $role = auth()->user()?->role; @endphp

            @if ($role === 'mahasiswa')
                <x-sidebar-link :href="route('mahasiswa.dashboard')" :active="request()->routeIs('mahasiswa.dashboard')" icon="layout-dashboard">
                    Dashboard
                </x-sidebar-link>

                <p class="mt-5 mb-1.5 px-3 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Pengajuan</p>
                <x-sidebar-link :href="route('mahasiswa.pengajuan.judul.create')" :active="request()->routeIs('mahasiswa.pengajuan.judul.*')" icon="file-text">
                    Judul Skripsi
                </x-sidebar-link>
                <x-sidebar-link :href="route('mahasiswa.pengajuan.aktif-kuliah.create')" :active="request()->routeIs('mahasiswa.pengajuan.aktif-kuliah.*')" icon="clipboard-list">
                    Surat Aktif Kuliah
                </x-sidebar-link>
                <x-sidebar-link :href="route('mahasiswa.pengajuan.izin-magang.create')" :active="request()->routeIs('mahasiswa.pengajuan.izin-magang.*')" icon="briefcase">
                    Surat Izin Magang
                </x-sidebar-link>
                <x-sidebar-link :href="route('mahasiswa.pengajuan.rekomendasi-magang.create')" :active="request()->routeIs('mahasiswa.pengajuan.rekomendasi-magang.*')" icon="award">
                    Surat Rekomendasi Magang
                </x-sidebar-link>
                <x-sidebar-link :href="route('mahasiswa.pengajuan.izin-penelitian.create')" :active="request()->routeIs('mahasiswa.pengajuan.izin-penelitian.*')" icon="microscope">
                    Izin Penelitian
                </x-sidebar-link>
                <x-sidebar-link :href="route('mahasiswa.pengajuan.seminar.create')" :active="request()->routeIs('mahasiswa.pengajuan.seminar.*')" icon="presentation">
                    Seminar Proposal
                </x-sidebar-link>
                <x-sidebar-link :href="route('mahasiswa.pengajuan.sidang.create')" :active="request()->routeIs('mahasiswa.pengajuan.sidang.*')" icon="landmark">
                    Sidang Skripsi
                </x-sidebar-link>

                <p class="mt-5 mb-1.5 px-3 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Riwayat</p>
                <x-sidebar-link :href="route('mahasiswa.riwayat.index')" :active="request()->routeIs('mahasiswa.riwayat.*') || request()->routeIs('mahasiswa.surat.*')" icon="history">
                    Riwayat Pengajuan
                </x-sidebar-link>
            @endif

            @if ($role === 'admin')
                @php
                    // Hitung notifikasi untuk admin — hanya query sekali
                    $badgeSuratMasuk = \App\Models\PengajuanSurat::where('status', 'diajukan')
                        ->whereIn('jenis_surat', ['aktif_kuliah', 'izin_magang', 'rekomendasi_magang', 'izin_penelitian'])
                        ->count();

                    $badgeJadwal = \App\Models\PengajuanSurat::whereIn('jenis_surat', ['seminar_proposal', 'sidang_skripsi'])
                        ->where(function ($q) {
                            $q->where('status', 'diajukan') // sidang butuh verifikasi berkas
                              ->orWhere(function ($q2) {
                                  $q2->where('status', 'disetujui')->whereNull('tanggal_jadwal'); // disetujui tapi belum ada jadwal
                              });
                        })
                        ->count();
                @endphp

                <x-sidebar-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard') && !request()->routeIs('admin.dashboard.rasio')" icon="layout-dashboard">
                    Dashboard
                </x-sidebar-link>

                <p class="mt-5 mb-1.5 px-3 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Surat</p>
                <x-sidebar-link :href="route('admin.surat.index')" :active="request()->routeIs('admin.surat.*')" icon="inbox" :badge="$badgeSuratMasuk ?: null">
                    Antrian Surat
                </x-sidebar-link>
                <x-sidebar-link :href="route('admin.jadwal.index')" :active="request()->routeIs('admin.jadwal.*')" icon="calendar-days" :badge="$badgeJadwal ?: null">
                    Jadwal Seminar/Sidang
                </x-sidebar-link>
                <x-sidebar-link :href="route('admin.buat-surat.create')" :active="request()->routeIs('admin.buat-surat.*')" icon="pen-line">
                    Buat Surat Langsung
                </x-sidebar-link>
                <x-sidebar-link :href="route('admin.arsip.index')" :active="request()->routeIs('admin.arsip.*')" icon="archive">
                    Arsip Surat
                </x-sidebar-link>
                <x-sidebar-link :href="route('admin.template-surat.index')" :active="request()->routeIs('admin.template-surat.*')" icon="file-code-2">
                    Template Surat
                </x-sidebar-link>

                <p class="mt-5 mb-1.5 px-3 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Master Data</p>
                <x-sidebar-link :href="route('admin.mahasiswa.index')" :active="request()->routeIs('admin.mahasiswa.*')" icon="users">
                    Data Mahasiswa
                </x-sidebar-link>
                <x-sidebar-link :href="route('admin.dosen.index')" :active="request()->routeIs('admin.dosen.*')" icon="user-round-check">
                    Data Dosen
                </x-sidebar-link>

                <p class="mt-5 mb-1.5 px-3 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Laporan</p>
                <x-sidebar-link :href="route('admin.dashboard.rasio')" :active="request()->routeIs('admin.dashboard.rasio')" icon="bar-chart-3">
                    Rasio Dosen
                </x-sidebar-link>

                <p class="mt-5 mb-1.5 px-3 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Sistem</p>
                <x-sidebar-link :href="route('admin.pengaturan.index')" :active="request()->routeIs('admin.pengaturan.*')" icon="settings">
                    Pengaturan
                </x-sidebar-link>
            @endif

            @if ($role === 'kaprodi')
                <x-sidebar-link :href="route('kaprodi.dashboard')" :active="request()->routeIs('kaprodi.dashboard') && !request()->routeIs('kaprodi.dashboard.rasio')" icon="layout-dashboard">
                    Dashboard
                </x-sidebar-link>

                <p class="mt-5 mb-1.5 px-3 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Akademik</p>
                <x-sidebar-link :href="route('kaprodi.akademik.index')" :active="request()->routeIs('kaprodi.akademik.*')" icon="clipboard-check">
                    Antrian Pengajuan
                </x-sidebar-link>

                <p class="mt-5 mb-1.5 px-3 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Laporan</p>
                <x-sidebar-link :href="route('kaprodi.dashboard.rasio')" :active="request()->routeIs('kaprodi.dashboard.rasio')" icon="bar-chart-3">
                    Rasio Dosen
                </x-sidebar-link>
            @endif
        </nav>

        {{-- User profile --}}
        <div class="border-t border-slate-100 px-3 py-3">
            <a href="{{ route(auth()->user()?->role . '.profil.show') }}"
               class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition-colors hover:bg-brand-50 group">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-100 text-xs font-bold text-brand-700">
                    {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-xs font-semibold text-slate-800">{{ auth()->user()?->name }}</p>
                    <p class="truncate text-[10px] capitalize text-slate-400">{{ auth()->user()?->role }}</p>
                </div>
                <x-icon name="chevron-right" class="h-3.5 w-3.5 text-slate-300 opacity-0 group-hover:opacity-100 transition-opacity" />
            </a>
        </div>
    </aside>

    {{-- Overlay mobile --}}
    <div id="sidebar-overlay"
         class="fixed inset-0 z-40 hidden bg-black/30 backdrop-blur-sm lg:hidden"
         onclick="toggleSidebar()"></div>

    {{-- ===================== MAIN AREA ===================== --}}
    <div class="flex flex-1 flex-col overflow-hidden">

        {{-- Top bar --}}
        <header class="flex h-16 shrink-0 items-center justify-between border-b border-slate-200 bg-white px-4 lg:px-6">
            <div class="flex items-center gap-3">
                <button onclick="toggleSidebar()"
                        class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden transition-colors"
                        aria-label="Toggle sidebar">
                    <x-icon name="menu" class="h-5 w-5" />
                </button>
                <h1 class="text-base font-semibold text-slate-700">
                    {{ $title ?? 'Dashboard' }}
                </h1>
            </div>

            <div class="flex items-center gap-2">
                <span class="hidden text-sm text-slate-400 lg:block">{{ auth()->user()?->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition-colors">
                        <x-icon name="log-out" class="h-4 w-4" />
                        <span class="hidden sm:inline">Keluar</span>
                    </button>
                </form>
            </div>
        </header>

        {{-- Flash inline --}}
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                 x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="mx-4 mt-4 flex items-center gap-2 rounded-xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-800 lg:mx-6">
                <x-icon name="circle-check" class="h-4 w-4 shrink-0 text-brand-600" />
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                 class="mx-4 mt-4 flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 lg:mx-6">
                <x-icon name="circle-x" class="h-4 w-4 shrink-0 text-red-500" />
                {{ session('error') }}
            </div>
        @endif
        @if (session('warning'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                 class="mx-4 mt-4 flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 lg:mx-6">
                <x-icon name="triangle-alert" class="h-4 w-4 shrink-0 text-amber-500" />
                {{ session('warning') }}
            </div>
        @endif

        {{-- Page Content --}}
        <main class="flex-1 overflow-y-auto p-4 lg:p-6">
            {{ $slot }}
        </main>
    </div>
</div>

<x-flash-message />

{{-- Loading overlay --}}
<div wire:loading.flex
     class="fixed inset-0 z-[9998] items-center justify-center bg-white/60 backdrop-blur-sm"
     wire:loading.delay.200ms>
    <div class="flex items-center gap-3 rounded-2xl bg-white px-5 py-3.5 shadow-xl ring-1 ring-slate-200">
        <svg class="h-5 w-5 animate-spin text-brand-500" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
        </svg>
        <span class="text-sm font-medium text-slate-600">Memproses...</span>
    </div>
</div>

@livewireScripts
@stack('scripts')
<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }
</script>
</body>
</html>
