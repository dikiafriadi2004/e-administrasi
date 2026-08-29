<x-app-layout>
    <x-slot name="title">Jadwal Seminar & Sidang</x-slot>

    <div class="space-y-4">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Jadwal Seminar & Sidang</h2>
            <p class="mt-1 text-sm text-slate-500">Semua jadwal yang sudah ditetapkan Kaprodi. Upload surat undangan hasil TTD ke masing-masing mahasiswa.</p>
        </div>


        <div class="mb-2 flex items-center justify-between gap-3">
            <x-per-page-selector :current="$perPage ?? 10" />
            <p class="text-xs text-slate-400">Total: {{ $jadwal->total() }} data</p>
        </div>
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Mahasiswa</th>
                        <th class="px-4 py-3 text-left">Jenis</th>
                        <th class="px-4 py-3 text-left">Jadwal</th>
                        <th class="px-4 py-3 text-left">Penguji</th>
                        <th class="px-4 py-3 text-left">Undangan</th>
                        <th class="px-4 py-3 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($jadwal as $p)
                        @php
                            $jenisList = ['seminar_proposal' => 'Seminar Proposal', 'sidang_skripsi' => 'Sidang Skripsi'];
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3">
                                <p class="font-medium text-slate-800">{{ $p->mahasiswa->user->name }}</p>
                                <p class="text-xs text-slate-400">{{ $p->mahasiswa->nim }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                    {{ $p->jenis_surat === 'seminar_proposal' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'bg-violet-50 text-violet-700 ring-1 ring-violet-200' }}">
                                    {{ $jenisList[$p->jenis_surat] ?? $p->jenis_surat }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-slate-800">{{ \Carbon\Carbon::parse($p->tanggal_jadwal)->locale('id')->isoFormat('D MMM Y') }}</p>
                                <p class="text-xs text-slate-400">{{ $p->waktu_jadwal }} · {{ $p->tempat_jadwal }}</p>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-600">
                                @if ($p->dosenPenguji)
                                    <p>I. {{ $p->dosenPenguji->nama }}</p>
                                @endif
                                @if ($p->dosenPenguji2)
                                    <p class="text-slate-400">II. {{ $p->dosenPenguji2->nama }}</p>
                                @endif
                                @if (! $p->dosenPenguji)
                                    <span class="text-slate-300 italic">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if ($p->file_scan)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-emerald-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 shrink-0"></span>
                                        Selesai — Bisa Diunduh
                                    </span>
                                @elseif ($p->file_docx)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-amber-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-400 animate-pulse shrink-0"></span>
                                        Menunggu Scan TTD
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700 ring-1 ring-red-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-400 shrink-0"></span>
                                        Belum Generate
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.jadwal.show', $p) }}"
                                   class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-medium text-brand-600 hover:bg-brand-50 transition-colors">
                                    Kelola
                                    <x-icon name="arrow-right" class="h-3 w-3" />
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center">
                                <div class="flex flex-col items-center gap-2 text-slate-400">
                                    <x-icon name="calendar-days" class="h-8 w-8" />
                                    <p class="text-sm">Belum ada jadwal yang ditetapkan Kaprodi.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($jadwal->hasPages())
            <div class="px-1">{{ $jadwal->links() }}</div>
        @endif
    </div>
</x-app-layout>
