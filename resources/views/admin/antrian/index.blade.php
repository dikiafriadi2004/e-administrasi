<x-app-layout>
    <x-slot name="title">Antrian Pengajuan</x-slot>

    <div class="space-y-6">

        {{-- ===== SURAT MASUK ===== --}}
        <div class="space-y-3">
            <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50">
                    <x-icon name="inbox" class="h-4 w-4 text-amber-600" />
                </div>
                <h2 class="text-sm font-bold text-slate-800">
                    Pengajuan Surat Baru
                    @if ($pengajuanSurat->count())
                        <span class="ml-1.5 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-700">
                            {{ $pengajuanSurat->count() }}
                        </span>
                    @endif
                </h2>
            </div>
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Mahasiswa</th>
                            <th class="px-4 py-3 text-left">Jenis Surat</th>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-left">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($pengajuanSurat as $ps)
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
                                    <p class="font-medium text-slate-800">{{ $ps->mahasiswa->user->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $ps->mahasiswa->nim }}</p>
                                </td>
                                <td class="px-4 py-3 text-slate-700">{{ $jenisList[$ps->jenis_surat] ?? $ps->jenis_surat }}</td>
                                <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $ps->created_at->format('d M Y') }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.surat.show', $ps) }}"
                                       class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-medium text-brand-600 hover:bg-brand-50 transition-colors">
                                        Tinjau
                                        <x-icon name="arrow-right" class="h-3 w-3" />
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center">
                                    <div class="flex flex-col items-center gap-2 text-slate-400">
                                        <x-icon name="inbox" class="h-7 w-7" />
                                        <p class="text-sm">Tidak ada antrian pengajuan surat.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
