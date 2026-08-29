<x-app-layout>
    <x-slot name="title">Tinjau Pengajuan Judul</x-slot>

    <div class="mb-4 flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('admin.surat.index') }}" class="hover:text-brand-600 transition-colors">Antrian</a>
        <x-icon name="chevron-right" class="h-4 w-4 text-slate-300" />
        <span class="text-slate-700 font-medium">Detail Judul</span>
    </div>

    <div class="mx-auto max-w-2xl space-y-5"
         x-data="{ modalVerifikasi: false, modalTolak: false }">

        {{-- Info Mahasiswa --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-start justify-between gap-3">
                <h2 class="text-base font-bold text-slate-800">Pengajuan Judul Skripsi</h2>
                <x-status-badge :status="$pengajuan->status" />
            </div>
            <dl class="space-y-3 text-sm">
                <div class="grid grid-cols-3 gap-2">
                    <dt class="font-medium text-slate-500">Mahasiswa</dt>
                    <dd class="col-span-2 text-slate-800">{{ $pengajuan->mahasiswa->user->name }}
                        <span class="text-slate-400">({{ $pengajuan->mahasiswa->nim }})</span></dd>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <dt class="font-medium text-slate-500">Judul</dt>
                    <dd class="col-span-2 leading-relaxed text-slate-800">{{ $pengajuan->judul }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <dt class="font-medium text-slate-500">Bidang Kajian</dt>
                    <dd class="col-span-2 text-slate-800">{{ $pengajuan->bidang_kajian }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <dt class="font-medium text-slate-500">Ringkasan</dt>
                    <dd class="col-span-2 text-sm leading-relaxed text-slate-700">{{ $pengajuan->ringkasan }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <dt class="font-medium text-slate-500">Diajukan</dt>
                    <dd class="col-span-2 text-slate-500">{{ $pengajuan->created_at->format('d M Y, H:i') }}</dd>
                </div>
                @if ($pengajuan->nama_file_pendukung)
                    <div class="grid grid-cols-3 gap-2">
                        <dt class="font-medium text-slate-500">Dok. Pendukung</dt>
                        <dd class="col-span-2">
                            <span class="rounded-lg bg-slate-100 px-2 py-0.5 font-mono text-xs text-slate-600">
                                {{ $pengajuan->nama_file_pendukung }}
                            </span>
                        </dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- Aksi --}}
        @if ($pengajuan->status === 'diajukan')
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="mb-4 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <x-icon name="zap" class="h-3.5 w-3.5" />
                    Aksi
                </h3>
                <div class="flex flex-wrap gap-3">
                    <button @click="modalVerifikasi = true"
                            class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600 transition-colors">
                        <x-icon name="check-circle" class="h-4 w-4" />
                        Verifikasi — Teruskan ke Kaprodi
                    </button>
                    <button @click="modalTolak = true"
                            class="inline-flex items-center gap-2 rounded-xl border border-red-200 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition-colors">
                        <x-icon name="x-circle" class="h-4 w-4" />
                        Tolak
                    </button>
                </div>
            </div>

            {{-- Modal Verifikasi --}}
            <div x-show="modalVerifikasi" x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4"
                 x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <div @click.stop class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5"
                     x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-brand-100">
                        <x-icon name="check-circle" class="h-6 w-6 text-brand-600" />
                    </div>
                    <h3 class="mb-2 text-base font-bold text-slate-900">Konfirmasi Verifikasi</h3>
                    <p class="mb-6 text-sm text-slate-500">
                        Pengajuan judul dari <strong class="text-slate-800">{{ $pengajuan->mahasiswa->user->name }}</strong> akan diteruskan ke Kaprodi untuk diputuskan.
                    </p>
                    <div class="flex justify-end gap-3">
                        <button @click="modalVerifikasi = false"
                                class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">Batal</button>
                        <form method="POST" action="{{ route('admin.surat.index') }}">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600 transition-colors">
                                <x-icon name="check" class="h-4 w-4" />
                                Ya, Verifikasi
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Modal Tolak --}}
            <div x-show="modalTolak" x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4"
                 x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <div @click.stop class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5"
                     x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-red-100">
                        <x-icon name="x-circle" class="h-6 w-6 text-red-600" />
                    </div>
                    <h3 class="mb-2 text-base font-bold text-slate-900">Tolak Pengajuan Judul</h3>
                    <p class="mb-4 text-sm text-slate-500">Berikan alasan penolakan yang jelas agar mahasiswa bisa memperbaiki pengajuannya.</p>
                    <form method="POST" action="{{ route('admin.surat.tolak', $pengajuan) }}" class="space-y-4">
                        @csrf
                        <textarea name="catatan_penolakan" rows="3" required
                                  placeholder="Contoh: Judul terlalu umum, tambahkan metode spesifik..."
                                  class="block w-full rounded-xl border-slate-200 text-sm focus:border-red-400 focus:ring-red-400">{{ old('catatan_penolakan') }}</textarea>
                        @error('catatan_penolakan') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        <div class="flex justify-end gap-3">
                            <button type="button" @click="modalTolak = false"
                                    class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">Batal</button>
                            <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 transition-colors">
                                <x-icon name="x-circle" class="h-4 w-4" />
                                Tolak
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- Riwayat Status --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
                <x-icon name="history" class="h-3.5 w-3.5" />
                Riwayat Status
            </h3>
            <ol class="relative ml-2 space-y-3 border-l border-slate-200">
                @forelse ($pengajuan->statusHistories as $h)
                    <li class="ml-4">
                        <div class="absolute -left-1.5 mt-1.5 h-3 w-3 rounded-full border-2 border-white bg-brand-400 shadow-sm"></div>
                        <p class="text-[10px] text-slate-400">{{ $h->created_at?->format('d M Y, H:i') }} — {{ $h->changedBy?->name }}</p>
                        <p class="text-xs font-medium text-slate-700">→ {{ $h->status_baru }}</p>
                        @if ($h->catatan) <p class="text-xs text-slate-500 mt-0.5">{{ $h->catatan }}</p> @endif
                    </li>
                @empty
                    <li class="ml-4 text-sm text-slate-400">Belum ada riwayat.</li>
                @endforelse
            </ol>
        </div>
    </div>
</x-app-layout>
