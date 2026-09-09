<x-app-layout>
    <x-slot name="title">Antrian Pengajuan Judul Skripsi</x-slot>

    <div class="space-y-5">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Antrian Pengajuan Judul Skripsi</h2>
                <p class="mt-0.5 text-sm text-slate-500">Periksa kelengkapan berkas sebelum diteruskan ke Kaprodi.</p>
            </div>
            <x-per-page-selector :current="$perPage" />
        </div>

        {{-- Tabel: Baru Masuk (belum diperiksa) --}}
        @if ($antrian->count())
            <div class="overflow-hidden rounded-2xl border border-amber-200 bg-white shadow-sm">
                <div class="flex items-center gap-2 border-b border-amber-100 bg-amber-50 px-4 py-2.5">
                    <x-icon name="inbox" class="h-4 w-4 text-amber-600" />
                    <span class="text-xs font-semibold uppercase tracking-wider text-amber-700">
                        Baru Masuk — Belum Diperiksa ({{ $antrian->total() }})
                    </span>
                </div>
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Mahasiswa</th>
                            <th class="px-4 py-3 text-left">Judul</th>
                            <th class="px-4 py-3 text-left">Berkas</th>
                            <th class="px-4 py-3 text-left">Diajukan</th>
                            <th class="px-4 py-3 text-left">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($antrian as $p)
                            <tr class="hover:bg-amber-50 transition-colors">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-800">{{ $p->mahasiswa->user->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $p->mahasiswa->nim }}</p>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-700 max-w-xs">
                                    {{ \Illuminate\Support\Str::limit($p->judul, 60) }}
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 {{ $p->berkas->count() > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                        <x-icon name="paperclip" class="h-3 w-3" />
                                        {{ $p->berkas->count() }} berkas
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-400 whitespace-nowrap">
                                    {{ $p->created_at->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.antrian-judul.show', $p) }}"
                                       class="inline-flex items-center gap-1.5 rounded-xl bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-600 transition-colors">
                                        <x-icon name="eye" class="h-3.5 w-3.5" />
                                        Periksa Berkas
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if ($antrian->hasPages())
                    <div class="px-4 py-2 border-t">{{ $antrian->links() }}</div>
                @endif
            </div>
        @else
            <div class="rounded-2xl border border-slate-200 bg-white px-6 py-10 text-center shadow-sm">
                <x-icon name="inbox" class="mx-auto h-8 w-8 text-slate-300 mb-2" />
                <p class="text-sm text-slate-400">Tidak ada pengajuan judul yang perlu diperiksa.</p>
            </div>
        @endif

        {{-- Tabel: Sudah Diverifikasi — Menunggu Kaprodi --}}
        @if ($menungguKaprodi->count())
            <div class="overflow-hidden rounded-2xl border border-sky-200 bg-white shadow-sm">
                <div class="flex items-center gap-2 border-b border-sky-100 bg-sky-50 px-4 py-2.5">
                    <x-icon name="clock" class="h-4 w-4 text-sky-600" />
                    <span class="text-xs font-semibold uppercase tracking-wider text-sky-700">
                        Sudah Diverifikasi — Menunggu Kaprodi ({{ $menungguKaprodi->total() }})
                    </span>
                </div>
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Mahasiswa</th>
                            <th class="px-4 py-3 text-left">Judul</th>
                            <th class="px-4 py-3 text-left">Diverifikasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($menungguKaprodi as $p)
                            <tr class="hover:bg-sky-50 transition-colors">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-800">{{ $p->mahasiswa->user->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $p->mahasiswa->nim }}</p>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-700 max-w-xs">
                                    {{ \Illuminate\Support\Str::limit($p->judul, 60) }}
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-400 whitespace-nowrap">
                                    {{ $p->updated_at->format('d M Y') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if ($menungguKaprodi->hasPages())
                    <div class="px-4 py-2 border-t">{{ $menungguKaprodi->links() }}</div>
                @endif
            </div>
        @endif
    </div>
</x-app-layout>
