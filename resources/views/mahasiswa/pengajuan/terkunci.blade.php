<x-app-layout>
    <x-slot name="title">{{ ($sudahSelesai ?? false) ? 'Status Pengajuan' : 'Akses Terkunci' }}</x-slot>

    <div class="flex min-h-[60vh] items-center justify-center">
        @if ($sudahSelesai ?? false)
            {{-- Sudah diproses / sedang berjalan — tampilan positif --}}
            <div class="max-w-md rounded-2xl border border-emerald-200 bg-emerald-50 p-8 text-center shadow-sm">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-xl bg-emerald-100">
                    <x-icon name="circle-check" class="h-7 w-7 text-emerald-600" />
                </div>
                <h2 class="mb-2 text-base font-bold text-emerald-800">Sedang Diproses</h2>
                <p class="mb-5 text-sm text-emerald-700 leading-relaxed">{{ $pesan }}</p>
                <a href="{{ $linkUrl ?? route('mahasiswa.riwayat.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    {{ $linkLabel ?? 'Lihat Riwayat' }}
                </a>
            </div>
        @else
            {{-- Belum bisa diakses — tampilan amber/lock --}}
            <div class="max-w-md rounded-2xl border border-amber-200 bg-amber-50 p-8 text-center shadow-sm">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-xl bg-amber-100">
                    <x-icon name="lock" class="h-7 w-7 text-amber-600" />
                </div>
                <h2 class="mb-2 text-base font-bold text-amber-800">Tahap Ini Belum Terbuka</h2>
                <p class="mb-5 text-sm text-amber-700 leading-relaxed">{{ $pesan }}</p>
                <a href="{{ $linkUrl ?? route('mahasiswa.riwayat.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700 transition-colors">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    {{ $linkLabel ?? 'Lihat Riwayat' }}
                </a>
            </div>
        @endif
    </div>
</x-app-layout>
