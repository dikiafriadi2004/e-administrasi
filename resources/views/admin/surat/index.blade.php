<x-app-layout>
    <x-slot name="title">Antrian Surat</x-slot>

    <div class="space-y-6">

        {{-- ===== SURAT MASUK ===== --}}
        <div class="space-y-3">
            <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50">
                    <x-icon name="inbox" class="h-4 w-4 text-amber-600" />
                </div>
                <h2 class="text-sm font-bold text-slate-800">Surat Masuk — Belum Diproses</h2>
                @if ($suratMasuk->count())
                    <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-700">
                        {{ $suratMasuk->total() }} menunggu
                    </span>
                @endif
            </div>

            <div class="mb-2 flex items-center justify-between gap-3">
                <x-per-page-selector :current="$perPage ?? 10" />
                <p class="text-xs text-slate-400">Total: {{ $suratMasuk->total() }} data</p>
            </div>
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Mahasiswa</th>
                            <th class="px-4 py-3 text-left">Jenis Surat</th>
                            <th class="px-4 py-3 text-left">Keperluan</th>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($suratMasuk as $s)
                            @php
                                $jenisList = [
                                    'aktif_kuliah'       => 'Aktif Kuliah',
                                    'izin_magang'        => 'Izin Magang / PKL',
                                    'rekomendasi_magang' => 'Rekomendasi Magang',
                                    'izin_penelitian'    => 'Izin Penelitian',
                                    'undangan_penguji'   => 'Undangan Penguji',
                                ];
                                // Ringkasan keperluan per jenis surat
                                $keperluan = match ($s->jenis_surat) {
                                    'aktif_kuliah'       => $s->data_form['keperluan'] ?? '—',
                                    'izin_magang',
                                    'rekomendasi_magang' => $s->data_form['nama_instansi'] ?? '—',
                                    'izin_penelitian'    => \Illuminate\Support\Str::limit($s->data_form['judul_penelitian'] ?? '—', 45),
                                    default              => '—',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-800">{{ $s->mahasiswa->user->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $s->mahasiswa->nim }}</p>
                                </td>
                                <td class="px-4 py-3 text-slate-700">{{ $jenisList[$s->jenis_surat] ?? $s->jenis_surat }}</td>
                                <td class="max-w-xs px-4 py-3 text-xs text-slate-500 truncate">{{ $keperluan }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-slate-500">{{ $s->created_at->format('d M Y') }}</td>
                                <td class="px-4 py-3"><x-status-badge :status="$s->status" /></td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.surat.show', $s) }}"
                                       class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-medium text-brand-600 hover:bg-brand-50 transition-colors">
                                        Proses
                                        <x-icon name="arrow-right" class="h-3 w-3" />
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center">
                                    <div class="flex flex-col items-center gap-2 text-slate-400">
                                        <x-icon name="inbox" class="h-7 w-7" />
                                        <p class="text-sm">Tidak ada surat baru yang menunggu diproses.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($suratMasuk->hasPages())
                <div class="px-4">{{ $suratMasuk->links() }}</div>
            @endif
        </div>

        {{-- ===== SURAT DALAM PROSES ===== --}}
        <div class="space-y-3">
            <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-50">
                    <x-icon name="clock" class="h-4 w-4 text-violet-600" />
                </div>
                <h2 class="text-sm font-bold text-slate-800">Surat Dalam Proses TTD</h2>
            </div>
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Mahasiswa</th>
                            <th class="px-4 py-3 text-left">Nomor Surat</th>
                            <th class="px-4 py-3 text-left">Jenis</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($suratProses as $s)
                            @php
                                $jenisList = [
                                    'aktif_kuliah'     => 'Aktif Kuliah',
                                    'seminar_proposal' => 'Seminar Proposal',
                                    'sidang_skripsi'   => 'Sidang Skripsi',
                                    'undangan_penguji' => 'Undangan Penguji',
                                ];
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-800">{{ $s->mahasiswa->user->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $s->mahasiswa->nim }}</p>
                                </td>
                                <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $s->nomor_surat ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $jenisList[$s->jenis_surat] ?? $s->jenis_surat }}</td>
                                <td class="px-4 py-3"><x-status-badge :status="$s->status" /></td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.surat.show', $s) }}"
                                       class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-medium text-brand-600 hover:bg-brand-50 transition-colors">
                                        Detail
                                        <x-icon name="arrow-right" class="h-3 w-3" />
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center">
                                    <div class="flex flex-col items-center gap-2 text-slate-400">
                                        <x-icon name="check-circle" class="h-7 w-7" />
                                        <p class="text-sm">Tidak ada surat dalam proses saat ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($suratProses->hasPages())
                <div class="px-4">{{ $suratProses->links() }}</div>
            @endif
        </div>

        {{-- Quick action --}}
        <div class="flex items-center gap-4 rounded-2xl border border-brand-100 bg-brand-50 p-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-100">
                <x-icon name="pen-line" class="h-5 w-5 text-brand-600" />
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-brand-900">Perlu membuat surat lain?</p>
                <p class="text-xs text-brand-600">Undangan Penguji, Seminar, Sidang, dan lainnya</p>
            </div>
            <a href="{{ route('admin.buat-surat.create') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600 transition-colors">
                <x-icon name="plus" class="h-4 w-4" />
                Buat Surat
            </a>
        </div>

    </div>
</x-app-layout>
