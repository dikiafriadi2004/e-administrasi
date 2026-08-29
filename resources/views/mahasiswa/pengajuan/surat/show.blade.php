<x-app-layout>
    <x-slot name="title">Detail Pengajuan Surat</x-slot>

    <div class="mb-4 flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('mahasiswa.riwayat.index') }}" class="hover:text-brand-600 transition-colors">Riwayat</a>
        <x-icon name="chevron-right" class="h-4 w-4 text-slate-300" />
        <span class="text-slate-700 font-medium">Detail Surat</span>
    </div>

    @php
        $jenisList = [
            'aktif_kuliah'     => 'Surat Aktif Kuliah',
            'seminar_proposal' => 'Seminar Proposal',
            'sidang_skripsi'   => 'Sidang Skripsi',
            'undangan_penguji' => 'Undangan Penguji',
        ];
        $bolehDownloadDokumen = $surat->file_docx && $surat->status !== 'ditolak';
    @endphp

    <div class="mx-auto max-w-2xl space-y-5">

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold text-slate-800">
                        {{ $jenisList[$surat->jenis_surat] ?? $surat->jenis_surat }}
                    </h2>
                    @if ($surat->nomor_surat)
                        <p class="mt-0.5 font-mono text-xs text-slate-400">{{ $surat->nomor_surat }}</p>
                    @endif
                </div>
                <x-status-badge :status="$surat->status" />
            </div>

            <dl class="space-y-3 text-sm">
                @if ($surat->pengajuanJudul)
                    <div class="grid grid-cols-3 gap-2">
                        <dt class="font-medium text-slate-500">Judul Skripsi</dt>
                        <dd class="col-span-2 text-slate-800">{{ $surat->pengajuanJudul->judul }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <dt class="font-medium text-slate-500">Pembimbing</dt>
                        <dd class="col-span-2 text-slate-800">{{ $surat->pengajuanJudul->dosenPembimbing?->nama ?? '—' }}</dd>
                    </div>
                @endif

                @if ($surat->jenis_surat === 'aktif_kuliah')
                    <div class="grid grid-cols-3 gap-2">
                        <dt class="font-medium text-slate-500">Keperluan</dt>
                        <dd class="col-span-2 text-slate-800">{{ $surat->data_form['keperluan'] ?? '—' }}</dd>
                    </div>
                    @if (! empty($surat->data_form['tujuan_instansi']))
                        <div class="grid grid-cols-3 gap-2">
                            <dt class="font-medium text-slate-500">Tujuan Instansi</dt>
                            <dd class="col-span-2 text-slate-800">{{ $surat->data_form['tujuan_instansi'] }}</dd>
                        </div>
                    @endif
                @endif

                @if (in_array($surat->jenis_surat, ['seminar_proposal', 'sidang_skripsi']))
                    <div class="grid grid-cols-3 gap-2">
                        <dt class="font-medium text-slate-500">Tanggal Rencana</dt>
                        <dd class="col-span-2 text-slate-800">{{ $surat->data_form['tanggal_rencana'] ?? '—' }}</dd>
                    </div>
                @endif

                @if ($surat->jenis_surat === 'sidang_skripsi')
                    <div class="grid grid-cols-3 gap-2">
                        <dt class="font-medium text-slate-500">Waktu</dt>
                        <dd class="col-span-2 text-slate-800">{{ $surat->data_form['waktu_rencana'] ?? '—' }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <dt class="font-medium text-slate-500">Tempat</dt>
                        <dd class="col-span-2 text-slate-800">{{ $surat->data_form['tempat'] ?? '—' }}</dd>
                    </div>
                    @if ($surat->dosenPenguji)
                        <div class="grid grid-cols-3 gap-2">
                            <dt class="font-medium text-slate-500">Penguji</dt>
                            <dd class="col-span-2 text-slate-800">{{ $surat->dosenPenguji->nama }}</dd>
                        </div>
                    @endif
                @endif

                @if ($surat->catatan_penolakan)
                    <div class="col-span-3 rounded-xl border border-red-200 bg-red-50 p-3">
                        <p class="mb-1 text-xs font-semibold text-red-700">Catatan Penolakan:</p>
                        <p class="text-sm text-red-700">{{ $surat->catatan_penolakan }}</p>
                    </div>
                @endif
            </dl>

            {{-- Download --}}
            @if ($bolehDownloadDokumen || $surat->file_scan)
                <div class="mt-5 flex flex-wrap gap-2 border-t border-slate-100 pt-4">
                    @if ($bolehDownloadDokumen)
                        <a href="{{ route('mahasiswa.surat.download', [$surat, 'docx']) }}"
                           class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50 transition-colors">
                            <x-icon name="download" class="h-3.5 w-3.5" />
                            Download DOCX
                        </a>
                    @endif
                    @if ($surat->file_scan)
                        <a href="{{ route('mahasiswa.surat.download', [$surat, 'scan']) }}"
                           class="inline-flex items-center gap-1.5 rounded-xl border border-brand-200 bg-brand-50 px-3 py-1.5 text-sm font-medium text-brand-700 hover:bg-brand-100 transition-colors">
                            <x-icon name="download" class="h-3.5 w-3.5" />
                            Download Scan Sudah TTD
                        </a>
                    @endif
                </div>
            @endif
        </div>

        {{-- Riwayat Status --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
                <x-icon name="history" class="h-3.5 w-3.5" />
                Riwayat Status
            </h3>
            <ol class="relative ml-2 space-y-3 border-l border-slate-200">
                @forelse ($surat->statusHistories as $history)
                    <li class="ml-4">
                        <div class="absolute -left-1.5 mt-1.5 h-3 w-3 rounded-full border-2 border-white bg-brand-400 shadow-sm"></div>
                        <p class="text-[10px] text-slate-400">{{ $history->created_at?->format('d M Y, H:i') }}</p>
                        <p class="text-xs font-medium text-slate-700">
                            Status: <span class="font-semibold">{{ $history->status_baru }}</span>
                        </p>
                        @if ($history->catatan)
                            <p class="text-xs text-slate-500 mt-0.5">{{ $history->catatan }}</p>
                        @endif
                    </li>
                @empty
                    <li class="ml-4 text-sm text-slate-400">Belum ada riwayat.</li>
                @endforelse
            </ol>
        </div>
    </div>
</x-app-layout>
