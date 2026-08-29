<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    <div class="space-y-6">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Selamat datang, {{ auth()->user()?->name }} 👋</h2>
            <p class="mt-1 text-sm text-slate-500">
                NIM: <strong class="text-slate-700">{{ auth()->user()->mahasiswa?->nim ?? '-' }}</strong>
                &nbsp;·&nbsp; Angkatan: <strong class="text-slate-700">{{ auth()->user()->mahasiswa?->angkatan ?? '-' }}</strong>
            </p>
        </div>

        {{-- Jadwal Mendatang --}}
        @if ($jadwalMendatang->count())
            <div class="rounded-2xl border border-brand-200 bg-brand-50 p-5 shadow-sm">
                <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-brand-800">
                    <x-icon name="calendar-days" class="h-4 w-4 text-brand-600" />
                    Jadwal Seminar / Sidang
                </h3>
                <div class="space-y-3">
                    @foreach ($jadwalMendatang as $jadwal)
                        @php
                            $jenisList = ['seminar_proposal' => 'Seminar Proposal', 'sidang_skripsi' => 'Sidang Skripsi'];
                        @endphp
                        <div class="rounded-xl border border-brand-100 bg-white px-4 py-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-brand-600">
                                        {{ $jenisList[$jadwal->jenis_surat] ?? $jadwal->jenis_surat }}
                                    </p>
                                    <p class="mt-1 text-sm font-bold text-slate-800">
                                        {{ \Carbon\Carbon::parse($jadwal->tanggal_jadwal)->locale('id')->isoFormat('dddd, D MMMM Y') }}
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        {{ $jadwal->waktu_jadwal }} · {{ $jadwal->tempat_jadwal }}
                                    </p>
                                    @if ($jadwal->catatan_kaprodi)
                                        <p class="mt-1 text-xs text-amber-600">
                                            <strong>Catatan:</strong> {{ $jadwal->catatan_kaprodi }}
                                        </p>
                                    @endif
                                </div>
                                @if ($jadwal->file_scan)
                                    <a href="{{ route('mahasiswa.surat.download', [$jadwal, 'scan']) }}"
                                       class="inline-flex shrink-0 items-center gap-1.5 rounded-xl border border-brand-200 bg-brand-50 px-3 py-2 text-xs font-semibold text-brand-700 hover:bg-brand-100 transition-colors">
                                        <x-icon name="download" class="h-3.5 w-3.5" />
                                        Download Undangan
                                    </a>
                                @else
                                    <span class="inline-flex shrink-0 items-center gap-1.5 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-600">
                                        <x-icon name="clock" class="h-3.5 w-3.5" />
                                        Undangan disiapkan admin
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Status Pengajuan Aktif --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-slate-700">
                <x-icon name="activity" class="h-4 w-4 text-brand-500" />
                Status Pengajuan Aktif
            </h3>

            @if (!$judulAktif && $suratAktif->isEmpty())
                <div class="flex flex-col items-center gap-2 py-4 text-slate-400">
                    <x-icon name="inbox" class="h-8 w-8" />
                    <p class="text-sm italic">Belum ada pengajuan aktif.</p>
                </div>
            @else
                <div class="space-y-2.5">
                    {{-- Judul --}}
                    @if ($judulAktif)
                        <div class="flex items-start justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-brand-100">
                                    <x-icon name="file-text" class="h-4 w-4 text-brand-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Pengajuan Judul</p>
                                    <p class="mt-0.5 text-sm font-medium leading-snug text-slate-800">{{ \Illuminate\Support\Str::limit($judulAktif->judul, 80) }}</p>
                                    @if ($judulAktif->status === 'disetujui' && $judulAktif->dosenPembimbing)
                                        <div class="mt-1.5 inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 border border-emerald-200 px-2.5 py-1">
                                            <x-icon name="user-check" class="h-3 w-3 text-emerald-600" />
                                            <p class="text-xs font-semibold text-emerald-700">
                                                Pembimbing: {{ $judulAktif->dosenPembimbing->nama }}
                                            </p>
                                        </div>
                                    @elseif ($judulAktif->dosenPembimbing)
                                        <p class="mt-1 text-xs text-slate-500">Pembimbing: {{ $judulAktif->dosenPembimbing->nama }}</p>
                                    @endif
                                    @if ($judulAktif->catatan_kaprodi)
                                        <p class="mt-1 text-xs text-amber-600"><strong>Catatan:</strong> {{ $judulAktif->catatan_kaprodi }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <x-status-badge :status="$judulAktif->status" />
                                @if (in_array($judulAktif->status, ['diajukan', 'ditolak']))
                                    <a href="{{ route('mahasiswa.pengajuan.judul.edit', $judulAktif) }}"
                                       class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-brand-600 hover:bg-brand-50 transition-colors">
                                        <x-icon name="pencil" class="h-3 w-3" />
                                        Revisi
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- Banner notifikasi pembimbing baru ditetapkan --}}
                        @if ($judulAktif->status === 'disetujui' && $judulAktif->dosenPembimbing)
                            @php
                                // Cek apakah seminar sudah diajukan
                                $sudahAjukanSeminar = \App\Models\PengajuanSurat::where('mahasiswa_id', auth()->user()->mahasiswa?->id)
                                    ->where('jenis_surat', 'seminar_proposal')
                                    ->whereNotIn('status', ['ditolak'])
                                    ->exists();
                            @endphp
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                                <div class="flex items-start gap-3">
                                    <x-icon name="bell" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600" />
                                    <div>
                                        <p class="text-sm font-semibold text-emerald-800">Dosen Pembimbing Sudah Ditetapkan</p>
                                        <p class="mt-0.5 text-xs text-emerald-700">
                                            Kaprodi telah menetapkan <strong>{{ $judulAktif->dosenPembimbing->nama }}</strong>
                                            sebagai dosen pembimbing skripsi Anda. Silakan lanjutkan proses bimbingan.
                                        </p>
                                        @if (! $sudahAjukanSeminar)
                                            <a href="{{ route('mahasiswa.pengajuan.seminar.create') }}"
                                               class="mt-2 inline-flex items-center gap-1.5 rounded-lg border border-emerald-300 bg-white px-3 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-100 transition-colors">
                                                <x-icon name="presentation" class="h-3.5 w-3.5" />
                                                Ajukan Seminar Proposal
                                            </a>
                                        @else
                                            <p class="mt-1.5 text-xs text-emerald-600 italic">Seminar Proposal sudah diajukan.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif

                    {{-- Surat aktif --}}
                    @foreach ($suratAktif as $surat)
                        @php
                            $jenisList = [
                                'aktif_kuliah'     => ['label' => 'Surat Aktif Kuliah', 'icon' => 'clipboard-list',  'color' => 'sky'],
                                'seminar_proposal' => ['label' => 'Seminar Proposal',    'icon' => 'presentation',    'color' => 'emerald'],
                                'sidang_skripsi'   => ['label' => 'Sidang Skripsi',      'icon' => 'landmark',        'color' => 'violet'],
                                'undangan_penguji' => ['label' => 'Undangan Penguji',    'icon' => 'mail',            'color' => 'amber'],
                            ];
                            $j = $jenisList[$surat->jenis_surat] ?? ['label' => $surat->jenis_surat, 'icon' => 'file', 'color' => 'slate'];
                        @endphp
                        <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-100">
                                    <x-icon :name="$j['icon']" class="h-4 w-4 text-slate-500" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">{{ $j['label'] }}</p>
                                    <p class="text-xs text-slate-400">Diajukan {{ $surat->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-status-badge :status="$surat->status" />
                                @if ($surat->file_scan)
                                    <a href="{{ route('mahasiswa.surat.download', [$surat, 'scan']) }}"
                                       class="inline-flex items-center gap-1.5 rounded-lg border border-brand-200 bg-brand-50 px-2.5 py-1 text-xs font-medium text-brand-700 hover:bg-brand-100 transition-colors">
                                        <x-icon name="download" class="h-3.5 w-3.5" />
                                        Unduh
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Akses Cepat --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-slate-700">
                <x-icon name="zap" class="h-4 w-4 text-brand-500" />
                Akses Cepat
            </h3>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('mahasiswa.pengajuan.judul.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-brand-200 bg-brand-50 px-3 py-2 text-xs font-medium text-brand-700 hover:bg-brand-100 transition-colors">
                    <x-icon name="file-text" class="h-3.5 w-3.5" />
                    Ajukan Judul
                </a>
                <a href="{{ route('mahasiswa.pengajuan.aktif-kuliah.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-sky-200 bg-sky-50 px-3 py-2 text-xs font-medium text-sky-700 hover:bg-sky-100 transition-colors">
                    <x-icon name="clipboard-list" class="h-3.5 w-3.5" />
                    Surat Aktif Kuliah
                </a>
                <a href="{{ route('mahasiswa.pengajuan.seminar.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-700 hover:bg-emerald-100 transition-colors">
                    <x-icon name="presentation" class="h-3.5 w-3.5" />
                    Seminar Proposal
                </a>
                <a href="{{ route('mahasiswa.pengajuan.sidang.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-violet-200 bg-violet-50 px-3 py-2 text-xs font-medium text-violet-700 hover:bg-violet-100 transition-colors">
                    <x-icon name="landmark" class="h-3.5 w-3.5" />
                    Sidang Skripsi
                </a>
                <a href="{{ route('mahasiswa.riwayat.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 transition-colors">
                    <x-icon name="history" class="h-3.5 w-3.5" />
                    Semua Riwayat
                </a>
            </div>
        </div>

        {{-- Panduan Alur --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-slate-700">
                <x-icon name="map" class="h-4 w-4 text-brand-500" />
                Alur Pengajuan
            </h3>
            <ol class="space-y-3">
                @foreach ([
                    ['Ajukan judul skripsi — tunggu persetujuan & penetapan pembimbing', 'file-text'],
                    ['Ajukan seminar proposal (terbuka setelah judul disetujui)', 'presentation'],
                    ['Ajukan sidang skripsi (terbuka setelah seminar selesai)', 'landmark'],
                    ['Download surat yang sudah ditandatangani kapan saja dari Riwayat', 'download'],
                ] as [$teks, $icon])
                    <li class="flex items-start gap-3">
                        <div class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-100">
                            <x-icon :name="$icon" class="h-3.5 w-3.5 text-brand-600" />
                        </div>
                        <p class="text-sm text-slate-600 leading-relaxed">{{ $teks }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</x-app-layout>
