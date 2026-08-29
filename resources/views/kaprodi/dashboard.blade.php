<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    <div class="space-y-6">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Selamat datang, {{ auth()->user()?->name }} 👋</h2>
            <p class="mt-1 text-sm text-slate-500">Pantau antrian pengajuan akademik dan rasio beban dosen.</p>
        </div>

        {{-- Stat cards --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <x-stat-card label="Antrian Pengajuan"         :value="$antrianCount"  icon="inbox"            color="yellow" />
            <x-stat-card label="Judul Disetujui Bulan Ini" :value="$judulDisetujui" icon="check-circle"     color="green" />
            <x-stat-card label="Total Dosen"               :value="$totalDosen"    icon="user-round-check" color="brand" />
        </div>

        {{-- Breakdown antrian --}}
        @if ($antrianCount > 0)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-slate-700">
                    <x-icon name="layers" class="h-4 w-4 text-brand-500" />
                    Rincian Antrian
                </h3>
                <div class="grid grid-cols-3 gap-3">
                    <a href="{{ route('kaprodi.akademik.index') }}"
                       class="rounded-xl border p-4 text-center transition-all hover:shadow-md {{ $antrianJudul > 0 ? 'border-brand-200 bg-brand-50' : 'border-slate-100 bg-slate-50' }}">
                        <p class="text-3xl font-bold {{ $antrianJudul > 0 ? 'text-brand-700' : 'text-slate-300' }}">{{ $antrianJudul }}</p>
                        <p class="mt-1 text-xs font-medium text-slate-500">Judul Skripsi</p>
                    </a>
                    <a href="{{ route('kaprodi.akademik.index') }}"
                       class="rounded-xl border p-4 text-center transition-all hover:shadow-md {{ $antrianSeminar > 0 ? 'border-emerald-200 bg-emerald-50' : 'border-slate-100 bg-slate-50' }}">
                        <p class="text-3xl font-bold {{ $antrianSeminar > 0 ? 'text-emerald-700' : 'text-slate-300' }}">{{ $antrianSeminar }}</p>
                        <p class="mt-1 text-xs font-medium text-slate-500">Seminar Proposal</p>
                    </a>
                    <a href="{{ route('kaprodi.akademik.index') }}"
                       class="rounded-xl border p-4 text-center transition-all hover:shadow-md {{ $antrianSidang > 0 ? 'border-violet-200 bg-violet-50' : 'border-slate-100 bg-slate-50' }}">
                        <p class="text-3xl font-bold {{ $antrianSidang > 0 ? 'text-violet-700' : 'text-slate-300' }}">{{ $antrianSidang }}</p>
                        <p class="mt-1 text-xs font-medium text-slate-500">Sidang Skripsi</p>
                    </a>
                </div>
            </div>
        @endif

        {{-- Top 3 dosen paling tersedia --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-slate-700">
                <x-icon name="user-round-check" class="h-4 w-4 text-brand-500" />
                Dosen Paling Tersedia (Top 3)
            </h3>
            @if ($topTersedia->isEmpty())
                <p class="text-sm text-slate-400 italic">Belum ada data dosen.</p>
            @else
                <div class="space-y-2">
                    @foreach ($topTersedia as $i => $dosen)
                        <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-2.5">
                            <div class="flex items-center gap-3">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-100 text-xs font-bold text-brand-700">{{ $i + 1 }}</span>
                                <span class="text-sm font-medium text-slate-800">{{ $dosen->nama }}</span>
                            </div>
                            <span class="text-xs text-slate-500">{{ $dosen->jumlah_bimbingan }} bimbingan aktif</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Akses cepat --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-slate-700">
                <x-icon name="zap" class="h-4 w-4 text-brand-500" />
                Akses Cepat
            </h3>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('kaprodi.akademik.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-medium text-amber-700 hover:bg-amber-100 transition-colors">
                    <x-icon name="clipboard-check" class="h-3.5 w-3.5" />
                    Antrian Pengajuan
                    @if ($antrianCount > 0)
                        <span class="flex h-5 min-w-5 items-center justify-center rounded-full bg-amber-500 px-1 text-xs font-bold text-white">{{ $antrianCount }}</span>
                    @endif
                </a>
                <a href="{{ route('kaprodi.dashboard.rasio') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-brand-200 bg-brand-50 px-3 py-2 text-xs font-medium text-brand-700 hover:bg-brand-100 transition-colors">
                    <x-icon name="bar-chart-3" class="h-3.5 w-3.5" />
                    Rasio Dosen Lengkap
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
