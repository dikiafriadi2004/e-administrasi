<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    <div class="space-y-6">

        {{-- Header --}}
        <div>
            <h2 class="text-xl font-bold text-slate-900">Selamat datang, {{ auth()->user()?->name }} 👋</h2>
            <p class="mt-1 text-sm text-slate-500">Ringkasan aktivitas sistem hari ini.</p>
        </div>

        {{-- Banner peringatan semester --}}
        @if ($peringatanSemester)
            <div class="flex items-start gap-4 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100">
                    <x-icon name="triangle-alert" class="h-5 w-5 text-amber-600" />
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-amber-900">Kalender Akademik perlu diperbarui</p>
                    <p class="mt-0.5 text-xs text-amber-700">
                        Semester <strong>{{ $semesterAktif }} {{ $tahunAkademik }}</strong> terakhir diubah pada <strong>{{ $terakhirUpdateSemester }}</strong>.
                    </p>
                    <a href="{{ route('admin.pengaturan.index') }}"
                       class="mt-2 inline-flex items-center gap-1.5 rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-600 transition-colors">
                        <x-icon name="settings" class="h-3.5 w-3.5" />
                        Perbarui Pengaturan
                    </a>
                </div>
            </div>
        @else
            <div class="flex items-center gap-3 rounded-2xl border border-brand-200 bg-brand-50 px-4 py-3">
                <x-icon name="calendar-check" class="h-4 w-4 shrink-0 text-brand-500" />
                <p class="text-xs text-brand-700">
                    Semester aktif: <strong>{{ $semesterAktif }} {{ $tahunAkademik }}</strong>
                    <a href="{{ route('admin.pengaturan.index') }}" class="ml-2 text-brand-600 underline hover:text-brand-800">Ubah</a>
                </p>
            </div>
        @endif

        {{-- Kartu statistik --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card label="Mahasiswa Aktif"   :value="$totalMahasiswaAktif" icon="users"            color="brand" />
            <x-stat-card label="Total Dosen"        :value="$totalDosen"          icon="user-round-check" color="blue" />
            <x-stat-card label="Surat Masuk"        :value="$suratMasuk"          icon="inbox"            color="yellow" />
            <x-stat-card label="Surat Bulan Ini"    :value="$suratBulanIni"       icon="file-check"       color="green" />
        </div>

        {{-- Notifikasi Jadwal Menunggu Surat Undangan --}}
        @if ($jadwalMenungguSurat > 0 || $jadwalMenungguScan > 0)
            <div class="space-y-2">
                @if ($jadwalMenungguSurat > 0)
                    <div class="flex items-start gap-4 rounded-2xl border border-red-200 bg-red-50 p-4">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-100">
                            <x-icon name="bell-ring" class="h-5 w-5 text-red-600" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-red-900">
                                {{ $jadwalMenungguSurat }} jadwal menunggu surat undangan dibuat
                            </p>
                            <p class="mt-0.5 text-xs text-red-700">
                                Kaprodi sudah menetapkan jadwal seminar/sidang. Buat surat undangan, cetak, minta TTD, lalu upload scan.
                            </p>
                            <a href="{{ route('admin.jadwal.index') }}"
                               class="mt-2 inline-flex items-center gap-1.5 rounded-lg bg-red-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-600 transition-colors">
                                <x-icon name="calendar-days" class="h-3.5 w-3.5" />
                                Kelola Jadwal Sekarang
                            </a>
                        </div>
                    </div>
                @endif
                @if ($jadwalMenungguScan > 0)
                    <div class="flex items-start gap-4 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100">
                            <x-icon name="upload" class="h-5 w-5 text-amber-600" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-amber-900">
                                {{ $jadwalMenungguScan }} surat undangan menunggu scan TTD diupload
                            </p>
                            <p class="mt-0.5 text-xs text-amber-700">
                                DOCX sudah digenerate dan dicetak. Upload hasil scan yang sudah ditandatangani Kaprodi.
                            </p>
                            <a href="{{ route('admin.jadwal.index') }}"
                               class="mt-2 inline-flex items-center gap-1.5 rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-600 transition-colors">
                                <x-icon name="calendar-days" class="h-3.5 w-3.5" />
                                Upload Scan Sekarang
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Akses cepat --}}
        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold text-slate-700">Akses Cepat</h3>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.surat.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-medium text-amber-700 hover:bg-amber-100 transition-colors">
                    <x-icon name="inbox" class="h-3.5 w-3.5" />
                    Antrian Surat
                    @if ($suratMasuk > 0)
                        <span class="flex h-5 min-w-5 items-center justify-center rounded-full bg-amber-500 px-1 text-xs font-bold text-white">{{ $suratMasuk }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.jadwal.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-{{ ($jadwalMenungguSurat + $jadwalMenungguScan) > 0 ? 'red-200 bg-red-50 text-red-700 hover:bg-red-100' : 'slate-200 bg-slate-50 text-slate-600 hover:bg-slate-100' }} px-3 py-2 text-xs font-medium transition-colors">
                    <x-icon name="calendar-days" class="h-3.5 w-3.5" />
                    Jadwal Seminar/Sidang
                    @if (($jadwalMenungguSurat + $jadwalMenungguScan) > 0)
                        <span class="flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-xs font-bold text-white">{{ $jadwalMenungguSurat + $jadwalMenungguScan }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.buat-surat.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-brand-200 bg-brand-50 px-3 py-2 text-xs font-medium text-brand-700 hover:bg-brand-100 transition-colors">
                    <x-icon name="pen-line" class="h-3.5 w-3.5" />
                    Buat Surat Langsung
                </a>
                <a href="{{ route('admin.dashboard.rasio') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-sky-200 bg-sky-50 px-3 py-2 text-xs font-medium text-sky-700 hover:bg-sky-100 transition-colors">
                    <x-icon name="bar-chart-3" class="h-3.5 w-3.5" />
                    Rasio Dosen
                </a>
                <a href="{{ route('admin.arsip.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 transition-colors">
                    <x-icon name="archive" class="h-3.5 w-3.5" />
                    Arsip Surat
                </a>
                <a href="{{ route('admin.mahasiswa.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 transition-colors">
                    <x-icon name="users" class="h-3.5 w-3.5" />
                    Data Mahasiswa
                </a>
            </div>
        </div>

    </div>
</x-app-layout>
