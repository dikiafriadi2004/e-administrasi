<x-app-layout>
    <x-slot name="title">Antrian Pengajuan Akademik</x-slot>

    <div class="space-y-6">

        {{-- Per-page selector -- berlaku untuk semua tabel --}}
        <div class="flex items-center justify-between">
            <h1 class="text-base font-bold text-slate-800">Antrian Pengajuan Akademik</h1>
            <x-per-page-selector :current="$perPage" />
        </div>

        {{-- ===== JUDUL SKRIPSI ===== --}}
        <div class="space-y-2">
            <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50">
                    <x-icon name="file-text" class="h-4 w-4 text-brand-600" />
                </div>
                <h2 class="text-sm font-bold text-slate-800">Pengajuan Judul Skripsi</h2>
                @if ($pengajuanJudul->total())
                    <span class="rounded-full bg-brand-100 px-2.5 py-0.5 text-xs font-bold text-brand-700">
                        {{ $pengajuanJudul->total() }} menunggu
                    </span>
                @endif
            </div>
            <div class="overflow-hidden rounded-xl border bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Mahasiswa</th>
                            <th class="px-4 py-3 text-left">Judul</th>
                            <th class="px-4 py-3 text-left">Bidang Kajian</th>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-left">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($pengajuanJudul as $pj)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-800">{{ $pj->mahasiswa->user->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $pj->mahasiswa->nim }}</p>
                                </td>
                                <td class="max-w-xs px-4 py-3">
                                    <p class="truncate text-gray-700" title="{{ $pj->judul }}">{{ $pj->judul }}</p>
                                </td>
                                <td class="px-4 py-3 text-gray-500">{{ $pj->bidang_kajian }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-500">{{ $pj->created_at->format('d M Y') }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('kaprodi.akademik.judul.show', $pj) }}"
                                       class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-medium text-brand-600 hover:bg-brand-50 transition-colors">
                                        Tinjau
                                        <x-icon name="arrow-right" class="h-3 w-3" />
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-sm text-gray-400">Tidak ada pengajuan judul baru.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($pengajuanJudul->hasPages())
                <div class="px-4">{{ $pengajuanJudul->links() }}</div>
            @endif
        </div>

        {{-- ===== SEMINAR PROPOSAL ===== --}}
        <div class="space-y-2">
            <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50">
                    <x-icon name="presentation" class="h-4 w-4 text-emerald-600" />
                </div>
                <h2 class="text-sm font-bold text-slate-800">Pengajuan Seminar Proposal</h2>
                @if ($pengajuanSeminar->total())
                    <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-700">
                        {{ $pengajuanSeminar->total() }} menunggu
                    </span>
                @endif
            </div>
            <div class="overflow-hidden rounded-xl border bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Mahasiswa</th>
                            <th class="px-4 py-3 text-left">Judul Skripsi</th>
                            <th class="px-4 py-3 text-left">Rencana Tanggal</th>
                            <th class="px-4 py-3 text-left">Tanggal Ajukan</th>
                            <th class="px-4 py-3 text-left">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($pengajuanSeminar as $ps)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-800">{{ $ps->mahasiswa->user->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $ps->mahasiswa->nim }}</p>
                                </td>
                                <td class="max-w-xs px-4 py-3 text-gray-600 text-xs">
                                    {{ $ps->pengajuanJudul?->judul ?? '—' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-500">
                                    {{ $ps->data_form['tanggal_rencana'] ?? '—' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-500">{{ $ps->created_at->format('d M Y') }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('kaprodi.akademik.seminar.show', $ps) }}"
                                       class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-medium text-brand-600 hover:bg-brand-50 transition-colors">
                                        Tinjau
                                        <x-icon name="arrow-right" class="h-3 w-3" />
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-sm text-gray-400">Tidak ada pengajuan seminar baru.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($pengajuanSeminar->hasPages())
                <div class="px-4">{{ $pengajuanSeminar->links() }}</div>
            @endif
        </div>

        {{-- ===== SIDANG SKRIPSI ===== --}}
        <div class="space-y-2">
            <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-50">
                    <x-icon name="landmark" class="h-4 w-4 text-violet-600" />
                </div>
                <h2 class="text-sm font-bold text-slate-800">Pengajuan Sidang Skripsi</h2>
                @if ($pengajuanSidang->total())
                    <span class="rounded-full bg-violet-100 px-2.5 py-0.5 text-xs font-bold text-violet-700">
                        {{ $pengajuanSidang->total() }} menunggu
                    </span>
                @endif
            </div>
            <div class="overflow-hidden rounded-xl border bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Mahasiswa</th>
                            <th class="px-4 py-3 text-left">Judul Skripsi</th>
                            <th class="px-4 py-3 text-left">Pembimbing</th>
                            <th class="px-4 py-3 text-left">Rencana Sidang</th>
                            <th class="px-4 py-3 text-left">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($pengajuanSidang as $ps)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-800">{{ $ps->mahasiswa->user->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $ps->mahasiswa->nim }}</p>
                                </td>
                                <td class="max-w-xs px-4 py-3 text-xs text-gray-600">
                                    {{ $ps->pengajuanJudul?->judul ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ $ps->pengajuanJudul?->dosenPembimbing?->nama ?? '—' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-500">
                                    {{ $ps->data_form['tanggal_rencana'] ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('kaprodi.akademik.sidang.show', $ps) }}"
                                       class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-medium text-brand-600 hover:bg-brand-50 transition-colors">
                                        Tinjau
                                        <x-icon name="arrow-right" class="h-3 w-3" />
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-sm text-gray-400">Tidak ada pengajuan sidang baru.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($pengajuanSidang->hasPages())
                <div class="px-4">{{ $pengajuanSidang->links() }}</div>
            @endif
        </div>

        {{-- ===== RIWAYAT YANG SUDAH DIPROSES ===== --}}
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100">
                    <x-icon name="history" class="h-4 w-4 text-slate-500" />
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-800">Riwayat Pengajuan yang Sudah Diproses</h2>
                    <p class="text-xs text-slate-400">Seminar & sidang yang sudah mendapat keputusan (disetujui/ditolak).</p>
                </div>
            </div>

            {{-- Riwayat Seminar --}}
            @if ($riwayatSeminar->total() > 0)
                <div class="overflow-hidden rounded-xl border bg-white shadow-sm">
                    <div class="border-b bg-slate-50 px-4 py-2.5 flex items-center gap-2">
                        <x-icon name="presentation" class="h-3.5 w-3.5 text-slate-500" />
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Seminar Proposal</span>
                        <span class="ml-auto text-xs text-slate-400">{{ $riwayatSeminar->total() }} total</span>
                    </div>
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-4 py-2 text-left">Mahasiswa</th>
                                <th class="px-4 py-2 text-left">Penguji</th>
                                <th class="px-4 py-2 text-left">Jadwal</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($riwayatSeminar as $r)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-2.5">
                                        <p class="font-medium text-slate-800">{{ $r->mahasiswa->user->name }}</p>
                                        <p class="text-xs text-slate-400">{{ $r->mahasiswa->nim }}</p>
                                    </td>
                                    <td class="px-4 py-2.5 text-xs text-slate-600">
                                        @if ($r->dosenPenguji)
                                            <p>I. {{ $r->dosenPenguji->nama }}</p>
                                        @endif
                                        @if ($r->dosenPenguji2)
                                            <p class="text-slate-400">II. {{ $r->dosenPenguji2->nama }}</p>
                                        @endif
                                        @if (! $r->dosenPenguji) <span class="text-slate-300">—</span> @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-xs text-slate-600">
                                        @if ($r->tanggal_jadwal)
                                            <p>{{ \Carbon\Carbon::parse($r->tanggal_jadwal)->locale('id')->isoFormat('D MMM Y') }}</p>
                                            <p class="text-slate-400">{{ $r->waktu_jadwal }} · {{ $r->tempat_jadwal }}</p>
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5"><x-status-badge :status="$r->status" /></td>
                                    <td class="px-4 py-2.5">
                                        <a href="{{ route('kaprodi.akademik.seminar.show', $r) }}"
                                           class="text-xs text-brand-600 hover:text-brand-700">Detail</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if ($riwayatSeminar->hasPages())
                        <div class="px-4 py-2">{{ $riwayatSeminar->links() }}</div>
                    @endif
                </div>
            @endif

            {{-- Riwayat Sidang --}}
            @if ($riwayatSidang->total() > 0)
                <div class="overflow-hidden rounded-xl border bg-white shadow-sm">
                    <div class="border-b bg-slate-50 px-4 py-2.5 flex items-center gap-2">
                        <x-icon name="landmark" class="h-3.5 w-3.5 text-slate-500" />
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Sidang Skripsi</span>
                        <span class="ml-auto text-xs text-slate-400">{{ $riwayatSidang->total() }} total</span>
                    </div>
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-4 py-2 text-left">Mahasiswa</th>
                                <th class="px-4 py-2 text-left">Penguji</th>
                                <th class="px-4 py-2 text-left">Jadwal</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($riwayatSidang as $r)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-2.5">
                                        <p class="font-medium text-slate-800">{{ $r->mahasiswa->user->name }}</p>
                                        <p class="text-xs text-slate-400">{{ $r->mahasiswa->nim }}</p>
                                    </td>
                                    <td class="px-4 py-2.5 text-xs text-slate-600">
                                        @if ($r->dosenPenguji)
                                            <p>I. {{ $r->dosenPenguji->nama }}</p>
                                        @endif
                                        @if ($r->dosenPenguji2)
                                            <p class="text-slate-400">II. {{ $r->dosenPenguji2->nama }}</p>
                                        @endif
                                        @if (! $r->dosenPenguji) <span class="text-slate-300">—</span> @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-xs text-slate-600">
                                        @if ($r->tanggal_jadwal)
                                            <p>{{ \Carbon\Carbon::parse($r->tanggal_jadwal)->locale('id')->isoFormat('D MMM Y') }}</p>
                                            <p class="text-slate-400">{{ $r->waktu_jadwal }} · {{ $r->tempat_jadwal }}</p>
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5"><x-status-badge :status="$r->status" /></td>
                                    <td class="px-4 py-2.5">
                                        <a href="{{ route('kaprodi.akademik.sidang.show', $r) }}"
                                           class="text-xs text-brand-600 hover:text-brand-700">Detail</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if ($riwayatSidang->hasPages())
                        <div class="px-4 py-2">{{ $riwayatSidang->links() }}</div>
                    @endif
                </div>
            @endif

            @if ($riwayatSeminar->total() === 0 && $riwayatSidang->total() === 0)
                <div class="rounded-xl border bg-white px-6 py-8 text-center text-sm text-slate-400 shadow-sm">
                    Belum ada riwayat pengajuan yang sudah diproses.
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
