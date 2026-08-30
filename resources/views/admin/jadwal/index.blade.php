<x-app-layout>
    <x-slot name="title">Jadwal Seminar & Sidang</x-slot>

    <div class="space-y-4">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Jadwal Seminar & Sidang</h2>
            <p class="mt-1 text-sm text-slate-500">Kelola jadwal seminar dan sidang skripsi. Tetapkan tanggal/waktu/tempat lalu buat surat undangan.</p>
        </div>

        <x-per-page-selector :current="$perPage ?? 10" />

        {{-- Tabel: Menunggu Jadwal --}}
        @if ($menungguJadwal->count())
            <div class="rounded-2xl border border-amber-200 bg-amber-50 overflow-hidden shadow-sm">
                <div class="flex items-center gap-2 border-b border-amber-200 bg-amber-100 px-4 py-2.5">
                    <x-icon name="clock" class="h-4 w-4 text-amber-600" />
                    <span class="text-xs font-semibold uppercase tracking-wider text-amber-700">
                        Menunggu Penetapan Jadwal ({{ $menungguJadwal->total() }})
                    </span>
                </div>
                <table class="min-w-full divide-y divide-amber-100 text-sm">
                    <thead class="bg-amber-50 text-xs font-semibold uppercase tracking-wider text-amber-600">
                        <tr>
                            <th class="px-4 py-3 text-left">Mahasiswa</th>
                            <th class="px-4 py-3 text-left">Jenis</th>
                            <th class="px-4 py-3 text-left">Penguji</th>
                            <th class="px-4 py-3 text-left">Disetujui</th>
                            <th class="px-4 py-3 text-left">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-amber-100 bg-white">
                        @foreach ($menungguJadwal as $p)
                            <tr class="hover:bg-amber-50 transition-colors">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-800">{{ $p->mahasiswa->user->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $p->mahasiswa->nim }}</p>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-600">
                                    {{ $p->jenis_surat === 'seminar_proposal' ? 'Seminar Proposal' : 'Sidang Skripsi' }}
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-600">
                                    <p>P1: {{ $p->dosenPenguji?->nama ?? '—' }}</p>
                                    @if ($p->dosenPenguji2)
                                        <p class="text-slate-400">P2: {{ $p->dosenPenguji2->nama }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-400">
                                    {{ $p->updated_at->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.jadwal.show', $p) }}"
                                       class="inline-flex items-center gap-1.5 rounded-xl bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-600 transition-colors">
                                        <x-icon name="calendar-plus" class="h-3.5 w-3.5" />
                                        Tetapkan Jadwal
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if ($menungguJadwal->hasPages())
                    <div class="px-4 py-2 border-t border-amber-100">{{ $menungguJadwal->links() }}</div>
                @endif
            </div>
        @endif

        {{-- Tabel: Sudah Terjadwal --}}
        {{-- Tabel: Sudah Terjadwal --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b bg-slate-50 px-4 py-2.5">
                <div class="flex items-center gap-2">
                    <x-icon name="calendar-days" class="h-4 w-4 text-slate-500" />
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                        Sudah Terjadwal ({{ $jadwal->total() }})
                    </span>
                </div>
            </div>
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
