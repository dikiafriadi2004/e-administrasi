<x-app-layout>
    <x-slot name="title">Detail Pengajuan Judul</x-slot>

    <div class="mb-4 flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('admin.antrian-judul.index') }}" class="hover:text-brand-600 transition-colors">Antrian Judul</a>
        <x-icon name="chevron-right" class="h-4 w-4 text-slate-300" />
        <span class="font-medium text-slate-700">{{ $pengajuan->mahasiswa->user->name }}</span>
    </div>

    <div class="mx-auto max-w-2xl space-y-5"
         x-data="{ modalVerifikasi: false, modalTolak: false }">

        {{-- Info Pengajuan --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-start justify-between gap-3">
                <h2 class="text-base font-bold text-slate-800">Pengajuan Judul Skripsi</h2>
                <x-status-badge :status="$pengajuan->status" />
            </div>

            <dl class="space-y-3 text-sm">
                <div class="grid grid-cols-3 gap-2">
                    <dt class="font-medium text-slate-500">Mahasiswa</dt>
                    <dd class="col-span-2 text-slate-800">
                        {{ $pengajuan->mahasiswa->user->name }}
                        <span class="text-slate-400 text-xs">({{ $pengajuan->mahasiswa->nim }})</span>
                    </dd>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <dt class="font-medium text-slate-500">Judul</dt>
                    <dd class="col-span-2 font-semibold text-slate-800 leading-snug">{{ $pengajuan->judul }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <dt class="font-medium text-slate-500">Bidang Kajian</dt>
                    <dd class="col-span-2 text-slate-700">{{ $pengajuan->bidang_kajian }}</dd>
                </div>
                @if ($pengajuan->ringkasan)
                    <div class="grid grid-cols-3 gap-2">
                        <dt class="font-medium text-slate-500">Ringkasan</dt>
                        <dd class="col-span-2 text-slate-600 text-xs leading-relaxed">{{ $pengajuan->ringkasan }}</dd>
                    </div>
                @endif
                @if ($pengajuan->pendekatan_penelitian)
                    <div class="grid grid-cols-3 gap-2">
                        <dt class="font-medium text-slate-500">Pendekatan</dt>
                        <dd class="col-span-2 text-slate-700">{{ $pengajuan->pendekatan_penelitian }}</dd>
                    </div>
                @endif
                @if ($pengajuan->nama_dosen_wali)
                    <div class="grid grid-cols-3 gap-2">
                        <dt class="font-medium text-slate-500">Dosen Wali</dt>
                        <dd class="col-span-2 text-slate-700">{{ $pengajuan->nama_dosen_wali }}</dd>
                    </div>
                @endif
                @if ($pengajuan->nama_dosen_spup)
                    <div class="grid grid-cols-3 gap-2">
                        <dt class="font-medium text-slate-500">Dosen SPUP</dt>
                        <dd class="col-span-2 text-slate-700">{{ $pengajuan->nama_dosen_spup }}</dd>
                    </div>
                @endif
                <div class="grid grid-cols-3 gap-2">
                    <dt class="font-medium text-slate-500">Diajukan</dt>
                    <dd class="col-span-2 text-slate-400 text-xs">{{ $pengajuan->created_at->format('d M Y, H:i') }}</dd>
                </div>
            </dl>
        </div>

        {{-- Berkas Syarat --}}
        @if ($pengajuan->berkas->count())
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <x-icon name="paperclip" class="h-3.5 w-3.5" />
                    Berkas yang Dilampirkan ({{ $pengajuan->berkas->count() }} file)
                </h3>
                <ul class="space-y-2">
                    @foreach ($pengajuan->berkas as $berkas)
                        <li class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 px-3 py-2.5">
                            <x-icon name="file" class="h-4 w-4 shrink-0 text-slate-400" />
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium text-slate-700 truncate">{{ $berkas->nama_asli }}</p>
                                <p class="text-[10px] text-slate-400">{{ $berkas->label }}</p>
                            </div>
                            <a href="{{ route('admin.berkas.download', $berkas) }}"
                               class="shrink-0 inline-flex items-center gap-1 rounded-lg border border-brand-200 bg-white px-2 py-1 text-xs font-medium text-brand-600 hover:bg-brand-50 transition-colors">
                                <x-icon name="download" class="h-3 w-3" />
                                Download
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @else
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4">
                <div class="flex items-center gap-2">
                    <x-icon name="alert-triangle" class="h-4 w-4 text-amber-500 shrink-0" />
                    <p class="text-sm text-amber-700">Mahasiswa tidak melampirkan berkas apapun.</p>
                </div>
            </div>
        @endif

        {{-- Panel Aksi --}}
        @if ($pengajuan->status === 'diajukan')
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <x-icon name="zap" class="h-3.5 w-3.5" />
                    Keputusan Verifikasi Berkas
                </h3>
                <p class="mb-4 text-xs text-slate-500">
                    Download dan periksa berkas di atas. Jika sudah lengkap dan sesuai, verifikasi untuk diteruskan ke Kaprodi.
                    Jika berkas kurang atau tidak sesuai, tolak dengan memberikan alasan yang jelas.
                </p>
                <div class="flex gap-3">
                    <button @click="modalVerifikasi = true"
                            class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors">
                        <x-icon name="check-circle" class="h-4 w-4" />
                        Berkas Lengkap — Teruskan ke Kaprodi
                    </button>
                    <button @click="modalTolak = true"
                            class="inline-flex items-center gap-2 rounded-xl border border-red-200 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition-colors">
                        <x-icon name="x-circle" class="h-4 w-4" />
                        Tolak
                    </button>
                </div>
            </div>
        @elseif ($pengajuan->status === 'diverifikasi_admin')
            <div class="rounded-2xl border border-sky-200 bg-sky-50 p-4">
                <div class="flex items-center gap-2">
                    <x-icon name="circle-check" class="h-5 w-5 text-sky-600" />
                    <p class="text-sm font-semibold text-sky-800">Sudah Diverifikasi — Menunggu Kaprodi Menetapkan Pembimbing</p>
                </div>
            </div>
        @elseif ($pengajuan->status === 'disetujui')
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                <div class="flex items-center gap-2">
                    <x-icon name="circle-check" class="h-5 w-5 text-emerald-600" />
                    <p class="text-sm font-semibold text-emerald-800">Judul Disetujui — Pembimbing Ditetapkan Kaprodi</p>
                </div>
            </div>
        @elseif ($pengajuan->status === 'ditolak')
            <div class="rounded-2xl border border-red-200 bg-red-50 p-4">
                <p class="text-xs font-semibold text-red-800 mb-1">Ditolak</p>
                <p class="text-xs text-red-700">{{ $pengajuan->catatan_penolakan }}</p>
            </div>
        @endif

        {{-- Riwayat Status --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
                <x-icon name="history" class="h-3.5 w-3.5" />
                Riwayat Status
            </h3>
            <ol class="relative ml-2 space-y-3 border-l border-slate-200">
                @forelse ($pengajuan->statusHistories as $h)
                    <li class="ml-4">
                        <div class="absolute -left-1.5 mt-1.5 h-3 w-3 rounded-full border-2 border-white bg-brand-400 shadow-sm"></div>
                        <p class="text-[10px] text-slate-400">
                            {{ $h->created_at?->format('d M Y, H:i') }}
                            @if ($h->changedBy) — {{ $h->changedBy->name }} @endif
                        </p>
                        <p class="text-xs font-medium text-slate-700">→ {{ $h->status_baru }}</p>
                        @if ($h->catatan) <p class="text-xs text-slate-500 mt-0.5">{{ $h->catatan }}</p> @endif
                    </li>
                @empty
                    <li class="ml-4 text-xs text-slate-400">Belum ada riwayat.</li>
                @endforelse
            </ol>
        </div>
    </div>

    {{-- Modal Verifikasi --}}
    <div x-show="modalVerifikasi" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
         x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        <div @click.stop class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100">
                <x-icon name="check-circle" class="h-6 w-6 text-emerald-600" />
            </div>
            <h3 class="mb-2 text-base font-bold text-slate-900">Konfirmasi Verifikasi Berkas</h3>
            <p class="mb-6 text-sm text-slate-500">
                Pengajuan judul dari <strong>{{ $pengajuan->mahasiswa->user->name }}</strong> akan diteruskan ke Kaprodi untuk penetapan pembimbing.
            </p>
            <div class="flex justify-end gap-3">
                <button @click="modalVerifikasi = false"
                        class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</button>
                <form method="POST" action="{{ route('admin.antrian-judul.verifikasi', $pengajuan) }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                        <x-icon name="check" class="h-4 w-4" />
                        Ya, Teruskan ke Kaprodi
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Tolak --}}
    <div x-show="modalTolak" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
         x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        <div @click.stop class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-red-100">
                <x-icon name="x-circle" class="h-6 w-6 text-red-600" />
            </div>
            <h3 class="mb-2 text-base font-bold text-slate-900">Tolak Pengajuan Judul</h3>
            <p class="mb-4 text-sm text-slate-500">Berikan alasan agar mahasiswa bisa memperbaiki pengajuannya.</p>
            <form method="POST" action="{{ route('admin.antrian-judul.tolak', $pengajuan) }}" class="space-y-4">
                @csrf
                <textarea name="catatan_penolakan" rows="3" required
                          placeholder="Contoh: Berkas SPUP belum dilampirkan..."
                          class="block w-full rounded-xl border-slate-200 text-sm focus:border-red-400 focus:ring-red-400">{{ old('catatan_penolakan') }}</textarea>
                @error('catatan_penolakan') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                <div class="flex justify-end gap-3">
                    <button type="button" @click="modalTolak = false"
                            class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                        <x-icon name="x-circle" class="h-4 w-4" />
                        Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
