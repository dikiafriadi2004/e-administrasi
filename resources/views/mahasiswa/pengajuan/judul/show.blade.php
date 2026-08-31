<x-app-layout>
    <x-slot name="title">Detail Pengajuan Judul</x-slot>

    <div class="mb-4 flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('mahasiswa.riwayat.index') }}" class="hover:text-brand-600 transition-colors">Riwayat</a>
        <x-icon name="chevron-right" class="h-4 w-4 text-slate-300" />
        <span class="font-medium text-slate-700">Detail Pengajuan Judul</span>
    </div>

    <div class="mx-auto max-w-2xl space-y-5">

        {{-- Info Judul --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-start justify-between gap-3">
                <h2 class="text-base font-bold text-slate-800">Pengajuan Judul Skripsi</h2>
                <x-status-badge :status="$pengajuan->status" />
            </div>

            <dl class="space-y-3 text-sm">
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
                    <dd class="col-span-2 leading-relaxed text-slate-700">{{ $pengajuan->ringkasan }}</dd>
                </div>
                @if ($pengajuan->dosenPembimbing)
                    <div class="grid grid-cols-3 gap-2">
                        <dt class="font-medium text-slate-500">Pembimbing 1</dt>
                        <dd class="col-span-2 text-slate-800">{{ $pengajuan->dosenPembimbing->nama }}</dd>
                    </div>
                @endif
                @if ($pengajuan->dosenPembimbing2)
                    <div class="grid grid-cols-3 gap-2">
                        <dt class="font-medium text-slate-500">Pembimbing 2</dt>
                        <dd class="col-span-2 text-slate-800">{{ $pengajuan->dosenPembimbing2->nama }}</dd>
                    </div>
                @endif

                {{-- Berkas yang diupload --}}
                @if ($pengajuan->berkas->count())
                    <div class="grid grid-cols-3 gap-2">
                        <dt class="font-medium text-slate-500">Berkas Syarat</dt>
                        <dd class="col-span-2 space-y-1">
                            @foreach ($pengajuan->berkas as $berkas)
                                <a href="{{ route('mahasiswa.berkas.download', $berkas) }}"
                                   class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs text-slate-600 hover:bg-slate-100 transition-colors">
                                    <x-icon name="file" class="h-3 w-3 text-slate-400" />
                                    {{ $berkas->nama_asli }}
                                    <x-icon name="download" class="h-3 w-3 text-brand-500" />
                                </a>
                            @endforeach
                        </dd>
                    </div>
                @elseif ($pengajuan->nama_file_pendukung)
                    <div class="grid grid-cols-3 gap-2">
                        <dt class="font-medium text-slate-500">Dokumen</dt>
                        <dd class="col-span-2 font-mono text-xs text-slate-600 rounded bg-slate-100 px-2 py-0.5 inline-block">
                            {{ $pengajuan->nama_file_pendukung }}
                        </dd>
                    </div>
                @endif

                @if ($pengajuan->catatan_kaprodi)
                    <div class="col-span-3 rounded-xl border border-amber-200 bg-amber-50 p-3">
                        <p class="mb-1 flex items-center gap-1.5 text-xs font-semibold text-amber-800">
                            <x-icon name="message-circle" class="h-3.5 w-3.5" />
                            Catatan Kaprodi:
                        </p>
                        <p class="text-sm text-amber-700">{{ $pengajuan->catatan_kaprodi }}</p>
                    </div>
                @endif

                @if ($pengajuan->catatan_penolakan)
                    <div class="col-span-3 rounded-xl border border-red-200 bg-red-50 p-3">
                        <p class="mb-1 flex items-center gap-1.5 text-xs font-semibold text-red-800">
                            <x-icon name="circle-x" class="h-3.5 w-3.5" />
                            Catatan Penolakan:
                        </p>
                        <p class="text-sm text-red-700">{{ $pengajuan->catatan_penolakan }}</p>
                    </div>
                @endif
            </dl>

            @if (in_array($pengajuan->status, ['diajukan', 'ditolak']))
                <div class="mt-4 border-t border-slate-100 pt-4">
                    <a href="{{ route('mahasiswa.pengajuan.judul.edit', $pengajuan) }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-medium text-amber-700 hover:bg-amber-100 transition-colors">
                        <x-icon name="pencil" class="h-4 w-4" />
                        Revisi Pengajuan
                    </a>
                </div>
            @elseif ($pengajuan->status === 'disetujui')
                <div class="mt-4 border-t border-slate-100 pt-4 flex gap-3">
                    <a href="{{ route('mahasiswa.pengajuan.judul.download-bukti', $pengajuan) }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 hover:bg-emerald-100 transition-colors">
                        <x-icon name="download" class="h-4 w-4" />
                        Download Bukti Persetujuan
                    </a>
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
                @forelse ($pengajuan->statusHistories as $history)
                    <li class="ml-4">
                        <div class="absolute -left-1.5 mt-1.5 h-3 w-3 rounded-full border-2 border-white bg-brand-400 shadow-sm"></div>
                        <p class="text-[10px] text-slate-400">{{ $history->created_at?->format('d M Y, H:i') }}</p>
                        <p class="text-xs font-medium text-slate-700">
                            → <span class="font-semibold">{{ $history->status_baru }}</span>
                        </p>
                        @if ($history->catatan)
                            <p class="mt-0.5 text-xs text-slate-500">{{ $history->catatan }}</p>
                        @endif
                    </li>
                @empty
                    <li class="ml-4 text-sm text-slate-400">Belum ada riwayat.</li>
                @endforelse
            </ol>
        </div>
    </div>
</x-app-layout>
