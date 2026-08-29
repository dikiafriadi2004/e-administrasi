<x-app-layout>
    <x-slot name="title">Tinjau Pengajuan Surat</x-slot>

    <div class="mb-4 flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('admin.surat.index') }}" class="hover:text-brand-600 transition-colors">Antrian</a>
        <x-icon name="chevron-right" class="h-4 w-4 text-slate-300" />
        <span class="text-slate-700 font-medium">Detail Surat</span>
    </div>

    @php
        $jenisList = [
            'aktif_kuliah'     => 'Aktif Kuliah',
            'seminar_proposal' => 'Seminar Proposal',
            'sidang_skripsi'   => 'Sidang Skripsi',
            'undangan_penguji' => 'Undangan Penguji',
        ];
    @endphp

    <div class="mx-auto max-w-2xl space-y-5"
         x-data="{ modalVerifikasi: false, modalTolak: false, modalGenerate: false, modalSelesai: false }">

        {{-- Info Surat --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold text-slate-800">{{ $jenisList[$pengajuan->jenis_surat] ?? $pengajuan->jenis_surat }}</h2>
                    @if ($pengajuan->nomor_surat)
                        <p class="mt-0.5 font-mono text-xs text-slate-400">{{ $pengajuan->nomor_surat }}</p>
                    @endif
                </div>
                <x-status-badge :status="$pengajuan->status" />
            </div>
            <dl class="space-y-3 text-sm">
                <div class="grid grid-cols-3 gap-2">
                    <dt class="font-medium text-slate-500">Mahasiswa</dt>
                    <dd class="col-span-2 text-slate-800">{{ $pengajuan->mahasiswa->user->name }}
                        <span class="text-slate-400">({{ $pengajuan->mahasiswa->nim }})</span></dd>
                </div>
                @if ($pengajuan->pengajuanJudul)
                    <div class="grid grid-cols-3 gap-2">
                        <dt class="font-medium text-slate-500">Judul Skripsi</dt>
                        <dd class="col-span-2 text-slate-800">{{ $pengajuan->pengajuanJudul->judul }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <dt class="font-medium text-slate-500">Pembimbing</dt>
                        <dd class="col-span-2 text-slate-800">{{ $pengajuan->pengajuanJudul->dosenPembimbing?->nama ?? '—' }}</dd>
                    </div>
                @endif
                @if ($pengajuan->jenis_surat === 'aktif_kuliah')
                    <div class="grid grid-cols-3 gap-2">
                        <dt class="font-medium text-slate-500">Keperluan</dt>
                        <dd class="col-span-2 text-slate-800">{{ $pengajuan->data_form['keperluan'] ?? '—' }}</dd>
                    </div>
                    @if (! empty($pengajuan->data_form['tujuan_instansi']))
                        <div class="grid grid-cols-3 gap-2">
                            <dt class="font-medium text-slate-500">Tujuan Instansi</dt>
                            <dd class="col-span-2 text-slate-800">{{ $pengajuan->data_form['tujuan_instansi'] }}</dd>
                        </div>
                    @endif
                @endif
                @if (in_array($pengajuan->jenis_surat, ['seminar_proposal', 'sidang_skripsi']))
                    <div class="grid grid-cols-3 gap-2">
                        <dt class="font-medium text-slate-500">Tanggal Rencana</dt>
                        <dd class="col-span-2 text-slate-800">{{ $pengajuan->data_form['tanggal_rencana'] ?? '—' }}</dd>
                    </div>
                @endif
                @if ($pengajuan->jenis_surat === 'sidang_skripsi')
                    <div class="grid grid-cols-3 gap-2">
                        <dt class="font-medium text-slate-500">Waktu</dt>
                        <dd class="col-span-2 text-slate-800">{{ $pengajuan->data_form['waktu_rencana'] ?? '—' }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <dt class="font-medium text-slate-500">Tempat</dt>
                        <dd class="col-span-2 text-slate-800">{{ $pengajuan->data_form['tempat'] ?? '—' }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- Aksi --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="mb-4 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
                <x-icon name="zap" class="h-3.5 w-3.5" />
                Aksi
            </h3>

            @if ($pengajuan->status === 'diajukan')
                <div class="flex flex-wrap gap-3">
                    <button @click="modalVerifikasi = true"
                            class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600 transition-colors">
                        <x-icon name="check-circle" class="h-4 w-4" />
                        Verifikasi
                    </button>
                    <button @click="modalTolak = true"
                            class="inline-flex items-center gap-2 rounded-xl border border-red-200 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition-colors">
                        <x-icon name="x-circle" class="h-4 w-4" />
                        Tolak
                    </button>
                </div>

            @elseif ($pengajuan->status === 'menunggu_ttd')
                <div class="space-y-3">
                    <form method="POST" action="{{ route('admin.surat.generate', $pengajuan) }}"
                          id="form-generate" class="space-y-3">
                        @csrf
                        <div>
                            <x-input-label for="nomor-surat-input" value="Nomor Surat *" />
                            <x-text-input id="nomor-surat-input" type="text" name="nomor_surat"
                                          class="mt-1 block w-full font-mono"
                                          value="{{ old('nomor_surat', $pengajuan->nomor_surat) }}"
                                          placeholder="001/UCI/FIK/TI/VIII/2026" required />
                            <p class="mt-1 text-xs text-slate-400">Format: urutan/institusi/fakultas/prodi/bulan/tahun</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" @click="modalGenerate = true"
                                    class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600 transition-colors">
                                <x-icon name="file-cog" class="h-4 w-4" />
                                Generate DOCX
                            </button>
                            @if ($pengajuan->file_docx)
                                <a href="{{ route('admin.surat.download', [$pengajuan, 'docx']) }}"
                                   class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 transition-colors">
                                    <x-icon name="download" class="h-3.5 w-3.5" />
                                    DOCX
                                </a>
                            @endif
                        </div>
                    </form>

                    <div class="rounded-xl border border-brand-100 bg-brand-50 p-4 space-y-3">
                        <p class="flex items-center gap-1.5 text-xs font-semibold text-brand-800">
                            <x-icon name="upload" class="h-4 w-4" />
                            Upload Scan Sudah TTD
                        </p>
                        <form method="POST" action="{{ route('admin.surat.upload-scan', $pengajuan) }}"
                              enctype="multipart/form-data" class="space-y-2">
                            @csrf
                            <input type="file" name="file_scan" accept=".pdf" required
                                   class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-brand-100 file:px-3 file:py-1 file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-200" />
                            <p class="text-[10px] text-brand-600">Format PDF · Maks 10 MB</p>
                            @error('file_scan') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            <button type="submit"
                                    class="flex items-center gap-1.5 rounded-xl bg-brand-500 px-4 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition-colors">
                                <x-icon name="upload" class="h-3.5 w-3.5" />
                                Upload Scan TTD
                            </button>
                        </form>
                    </div>
                </div>

            @elseif ($pengajuan->status === 'sudah_ditandatangani')
                <div class="flex flex-wrap gap-2">
                    @if ($pengajuan->file_scan)
                        <a href="{{ route('admin.surat.download', [$pengajuan, 'scan']) }}"
                           class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700 hover:bg-emerald-100 transition-colors">
                            <x-icon name="download" class="h-4 w-4" />
                            Download Scan TTD
                        </a>
                    @endif
                    <button @click="modalSelesai = true"
                            class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors">
                        <x-icon name="check-circle" class="h-4 w-4" />
                        Tandai Selesai
                    </button>
                </div>

            @else
                <p class="text-sm text-slate-400 italic">Status: <strong>{{ $pengajuan->status }}</strong></p>
            @endif
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
                    Surat dari <strong class="text-slate-800">{{ $pengajuan->mahasiswa->user->name }}</strong> akan diteruskan ke Kaprodi.
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
                <h3 class="mb-2 text-base font-bold text-slate-900">Tolak Pengajuan Surat</h3>
                <p class="mb-4 text-sm text-slate-500">Berikan alasan penolakan yang jelas.</p>
                <form method="POST" action="{{ route('admin.surat.tolak', $pengajuan) }}" class="space-y-4">
                    @csrf
                    <textarea name="catatan_penolakan" rows="3" required placeholder="Jelaskan alasan penolakan..."
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

        {{-- Modal Generate --}}
        <div x-show="modalGenerate" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div @click.stop class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5"
                 x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-brand-100">
                    <x-icon name="file-cog" class="h-6 w-6 text-brand-600" />
                </div>
                <h3 class="mb-2 text-base font-bold text-slate-900">Generate Dokumen Surat</h3>
                <p class="mb-6 text-sm text-slate-500">File .docx akan dibuat dari template. Pastikan nomor surat sudah benar.</p>
                <div class="flex justify-end gap-3">
                    <button @click="modalGenerate = false"
                            class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">Batal</button>
                    <button @click="modalGenerate = false; document.getElementById('form-generate').submit()"
                            class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600 transition-colors">
                        <x-icon name="file-cog" class="h-4 w-4" />
                        Ya, Generate
                    </button>
                </div>
            </div>
        </div>

        {{-- Modal Selesai --}}
        <div x-show="modalSelesai" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div @click.stop class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5"
                 x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100">
                    <x-icon name="flag" class="h-6 w-6 text-emerald-600" />
                </div>
                <h3 class="mb-2 text-base font-bold text-slate-900">Tandai Selesai</h3>
                <p class="mb-6 text-sm text-slate-500">Mahasiswa dapat mengunduh scan surat kapan saja setelah ini.</p>
                <div class="flex justify-end gap-3">
                    <button @click="modalSelesai = false"
                            class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">Batal</button>
                    <form method="POST" action="{{ route('admin.surat.selesaikan', $pengajuan) }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors">
                            <x-icon name="check-circle" class="h-4 w-4" />
                            Ya, Selesaikan
                        </button>
                    </form>
                </div>
            </div>
        </div>

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
