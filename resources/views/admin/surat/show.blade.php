<x-app-layout>
    <x-slot name="title">Detail Surat — {{ $jenisLabel }}</x-slot>

    {{-- Breadcrumb --}}
    <div class="mb-4 flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('admin.surat.index') }}" class="hover:text-brand-600 transition-colors">Antrian Surat</a>
        <x-icon name="chevron-right" class="h-4 w-4 text-slate-300" />
        <span class="text-slate-700 font-medium">{{ $jenisLabel }}</span>
    </div>

    @php
        $mahasiswa      = $surat->mahasiswa;
        $user           = $mahasiswa->user;
        $pengajuanJudul = $surat->pengajuanJudul;
        $pembimbing     = $pengajuanJudul?->dosenPembimbing;
        $pembimbing2    = $pengajuanJudul?->dosenPembimbing2;
        $penguji        = $surat->dosenPenguji;
        $penguji2       = $surat->dosenPenguji2;
        $dataForm       = $surat->data_form ?? [];
    @endphp

    <div class="flex gap-5 items-start"
         x-data="{
             modalTolak: false, modalGenerate: false, modalSelesai: false,
             nomorUrutan: '{{ old('nomor_urutan') }}',
             nomorSuffix: '{{ $nomorSuffix }}',
             get nomorPenuh() { return (this.nomorUrutan || '...') + this.nomorSuffix; }
         }"
         x-init="$watch('nomorUrutan', v => {
             const u = new URL($refs.previewFrame.src);
             u.searchParams.set('nomor_urut', v);
             $refs.previewFrame.src = u.toString();
         })">

        {{-- ===== KOLOM KIRI ===== --}}
        <div class="w-80 shrink-0 space-y-4">

            {{-- Info singkat --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="mb-3 flex items-start justify-between gap-2">
                    <h2 class="text-sm font-bold text-slate-800">{{ $jenisLabel }}</h2>
                    <x-status-badge :status="$surat->status" />
                </div>
                <dl class="space-y-2 text-xs">
                    <div class="flex gap-2">
                        <dt class="w-24 shrink-0 font-medium text-slate-500">Mahasiswa</dt>
                        <dd class="text-slate-800">{{ $user->name }} <span class="text-slate-400">({{ $mahasiswa->nim }})</span></dd>
                    </div>
                    @if ($surat->jenis_surat === 'aktif_kuliah')
                        <div class="flex gap-2">
                            <dt class="w-24 shrink-0 font-medium text-slate-500">Keperluan</dt>
                            <dd class="text-slate-700">{{ $dataForm['keperluan'] ?? '—' }}</dd>
                        </div>
                        @if (! empty($dataForm['tujuan_instansi']))
                            <div class="flex gap-2">
                                <dt class="w-24 shrink-0 font-medium text-slate-500">Ditujukan</dt>
                                <dd class="text-slate-700">{{ $dataForm['tujuan_instansi'] }}</dd>
                            </div>
                        @endif
                    @elseif (in_array($surat->jenis_surat, ['izin_magang', 'rekomendasi_magang']))
                        <div class="flex gap-2">
                            <dt class="w-24 shrink-0 font-medium text-slate-500">Instansi</dt>
                            <dd class="text-slate-700">{{ $dataForm['nama_instansi'] ?? '—' }}</dd>
                        </div>
                        @if (! empty($dataForm['tanggal_mulai']))
                            <div class="flex gap-2">
                                <dt class="w-24 shrink-0 font-medium text-slate-500">Periode</dt>
                                <dd class="text-slate-700">
                                    {{ \Carbon\Carbon::parse($dataForm['tanggal_mulai'])->format('d M Y') }}
                                    s.d.
                                    {{ \Carbon\Carbon::parse($dataForm['tanggal_selesai'])->format('d M Y') }}
                                </dd>
                            </div>
                        @endif
                    @elseif ($surat->jenis_surat === 'izin_penelitian')
                        <div class="flex gap-2">
                            <dt class="w-24 shrink-0 font-medium text-slate-500">Judul</dt>
                            <dd class="text-slate-700 leading-snug">{{ $dataForm['judul_penelitian'] ?? '—' }}</dd>
                        </div>
                        @if (! empty($dataForm['bidang_penelitian']))
                            <div class="flex gap-2">
                                <dt class="w-24 shrink-0 font-medium text-slate-500">Bidang</dt>
                                <dd class="text-slate-700">{{ $dataForm['bidang_penelitian'] }}</dd>
                            </div>
                        @endif
                        <div class="flex gap-2">
                            <dt class="w-24 shrink-0 font-medium text-slate-500">Instansi</dt>
                            <dd class="text-slate-700">{{ $dataForm['nama_instansi'] ?? '—' }}</dd>
                        </div>
                    @endif
                    <div class="flex gap-2">
                        <dt class="w-24 shrink-0 font-medium text-slate-500">Diajukan</dt>
                        <dd class="text-slate-400">{{ $surat->created_at->format('d M Y, H:i') }}</dd>
                    </div>
                    @if ($surat->nomor_surat)
                        <div class="flex gap-2">
                            <dt class="w-24 shrink-0 font-medium text-slate-500">Nomor Urut</dt>
                            <dd class="font-mono text-slate-700">{{ $surat->nomor_surat }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Berkas Syarat dari Mahasiswa --}}
            @if ($surat->berkas->count())
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <h3 class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <x-icon name="paperclip" class="h-3.5 w-3.5" />
                        Berkas Syarat
                    </h3>
                    <ul class="space-y-1.5">
                        @foreach ($surat->berkas as $berkas)
                            <li class="flex items-center gap-2 rounded-xl border border-slate-100 bg-slate-50 px-3 py-2 text-xs">
                                <x-icon name="file" class="h-3.5 w-3.5 shrink-0 text-slate-400" />
                                <span class="flex-1 truncate text-slate-700">{{ $berkas->nama_asli }}</span>
                                <a href="{{ route('admin.berkas.download', $berkas) }}"
                                   class="shrink-0 text-brand-600 hover:text-brand-700">
                                    <x-icon name="download" class="h-3.5 w-3.5" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Panel Aksi --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <h3 class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <x-icon name="zap" class="h-3.5 w-3.5" />
                    Aksi
                </h3>

                @if ($surat->status === 'diajukan')
                    <div class="rounded-xl border border-brand-100 bg-brand-50 p-3 space-y-3">
                        <p class="flex items-center gap-1.5 text-xs font-semibold text-brand-800">
                            <x-icon name="circle-1" class="h-4 w-4" />
                            Isi Nomor Urutan & Generate
                        </p>
                        <form method="POST" action="{{ route('admin.surat.generate', $surat) }}" id="form-generate" class="space-y-2">
                            @csrf
                            <div class="flex items-center gap-0">
                                <input type="text" name="nomor_urutan"
                                       x-model="nomorUrutan"
                                       value="{{ old('nomor_urutan') }}"
                                       placeholder="2032"
                                       class="w-20 rounded-l-xl border border-r-0 border-slate-200 bg-white px-2 py-1.5 text-xs font-mono shadow-sm focus:border-brand-400 focus:ring-brand-400"
                                       required />
                                <span class="flex-1 truncate rounded-r-xl border border-slate-200 bg-slate-50 px-2 py-1.5 text-xs font-mono text-slate-500"
                                      x-text="nomorSuffix"></span>
                            </div>
                            <p class="text-[10px] text-slate-400">
                                Preview: <span class="font-mono font-medium text-brand-600" x-text="nomorPenuh"></span>
                            </p>
                            <p class="text-[10px] text-slate-400">
                                Di template Word: <code class="rounded bg-slate-100 px-1 font-mono">${nomor_urut}/${kode_institusi}/${kode_prodi}/${bulan_surat}/${tahun_surat}</code>
                            </p>
                            @error('nomor_urutan') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            <div class="flex gap-2">
                                <button type="button" @click="modalGenerate = true"
                                        class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl bg-brand-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition-colors">
                                    <x-icon name="file-cog" class="h-3.5 w-3.5" />
                                    Generate DOCX
                                </button>
                                <button type="button" @click="modalTolak = true"
                                        class="rounded-xl border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 transition-colors">
                                    <x-icon name="x" class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </form>
                    </div>

                @elseif ($surat->status === 'menunggu_ttd')
                    <div class="space-y-3">
                        @if ($surat->file_docx)
                            <a href="{{ route('admin.surat.download', [$surat, 'docx']) }}"
                               class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-100 transition-colors">
                                <x-icon name="download" class="h-3.5 w-3.5 text-slate-500" />
                                Download DOCX untuk Dicetak
                            </a>
                        @endif
                        <div class="rounded-xl border border-brand-100 bg-brand-50 p-3 space-y-2">
                            <p class="flex items-center gap-1.5 text-xs font-semibold text-brand-800">
                                <x-icon name="circle-2" class="h-4 w-4" />
                                Upload Scan Sudah TTD
                            </p>
                            <form method="POST" action="{{ route('admin.surat.upload-scan', $surat) }}"
                                  enctype="multipart/form-data" class="space-y-2">
                                @csrf
                                <input type="file" name="file_scan" accept=".pdf" required
                                       class="block w-full text-xs file:mr-2 file:rounded-lg file:border-0 file:bg-brand-100 file:px-2 file:py-1 file:text-xs file:font-medium file:text-brand-700" />
                                <p class="text-[10px] text-brand-600">Format PDF · Maks 10 MB</p>
                                @error('file_scan') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                <button type="submit"
                                        class="flex w-full items-center justify-center gap-1.5 rounded-xl bg-brand-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition-colors">
                                    <x-icon name="upload" class="h-3.5 w-3.5" />
                                    Upload Scan TTD
                                </button>
                            </form>
                        </div>
                    </div>

                @elseif ($surat->status === 'sudah_ditandatangani')
                    <div class="space-y-2">
                        @if ($surat->file_scan)
                            <a href="{{ route('admin.surat.download', [$surat, 'scan']) }}"
                               class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-700 hover:bg-emerald-100 transition-colors">
                                <x-icon name="download" class="h-3.5 w-3.5" />
                                Download Scan TTD
                            </a>
                        @endif
                        <button @click="modalSelesai = true"
                                class="flex w-full items-center justify-center gap-1.5 rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700 transition-colors">
                            <x-icon name="check-circle" class="h-3.5 w-3.5" />
                            Tandai Selesai
                        </button>
                    </div>

                @elseif ($surat->status === 'selesai')
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3">
                        <div class="flex items-center gap-2 mb-2">
                            <x-icon name="circle-check" class="h-4 w-4 text-emerald-600" />
                            <p class="text-xs font-semibold text-emerald-800">Surat Selesai</p>
                        </div>
                        @if ($surat->file_scan)
                            <a href="{{ route('admin.surat.download', [$surat, 'scan']) }}"
                               class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-300 bg-white px-2.5 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-50 transition-colors">
                                <x-icon name="download" class="h-3 w-3" />
                                Download Scan
                            </a>
                        @endif
                    </div>

                @elseif ($surat->status === 'ditolak')
                    <div class="rounded-xl border border-red-200 bg-red-50 p-3">
                        <div class="flex items-center gap-2 mb-1">
                            <x-icon name="circle-x" class="h-4 w-4 text-red-500" />
                            <p class="text-xs font-semibold text-red-800">Ditolak</p>
                        </div>
                        <p class="text-xs text-red-700">{{ $surat->catatan_penolakan }}</p>
                    </div>
                @endif
            </div>

            {{-- Riwayat Status --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <h3 class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <x-icon name="history" class="h-3.5 w-3.5" />
                    Riwayat Status
                </h3>
                <ol class="relative ml-2 space-y-3 border-l border-slate-200">
                    @forelse ($surat->statusHistories as $h)
                        <li class="ml-4">
                            <div class="absolute -left-1.5 mt-1 h-3 w-3 rounded-full border-2 border-white bg-brand-400 shadow-sm"></div>
                            <p class="text-[10px] text-slate-400">{{ $h->created_at?->format('d M Y, H:i') }}</p>
                            <p class="text-xs font-medium text-slate-700">→ {{ $h->status_baru }}</p>
                            @if ($h->catatan) <p class="text-xs text-slate-500 mt-0.5">{{ $h->catatan }}</p> @endif
                        </li>
                    @empty
                        <li class="ml-4 text-xs text-slate-400">Belum ada riwayat.</li>
                    @endforelse
                </ol>
            </div>
        </div>

        {{-- ===== KOLOM KANAN: Preview ===== --}}
        <div class="flex-1 min-w-0">
            <div class="mb-2 flex items-center justify-between">
                <p class="flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-slate-500">
                    <x-icon name="eye" class="h-3.5 w-3.5" />
                    Pratinjau Surat
                </p>
                <p class="text-xs text-slate-400">Preview otomatis menggunakan data terkini</p>
            </div>

            @php
                $previewParams = http_build_query(array_filter([
                    'jenis'             => $surat->jenis_surat,
                    'nomor_surat'       => $surat->nomor_surat ?? '',
                    'nomor_urut'        => $surat->nomor_surat ?? '',
                    'nama_mahasiswa'    => $user->name,
                    'nim'               => $mahasiswa->nim,
                    'angkatan'          => (string) $mahasiswa->angkatan,
                    'alamat_mahasiswa'  => $mahasiswa->alamat ?? '',
                    'keperluan'         => $dataForm['keperluan'] ?? '',
                    'tujuan_instansi'   => $dataForm['tujuan_instansi'] ?? '',
                    'nama_instansi'     => $dataForm['nama_instansi'] ?? '',
                    'alamat_instansi'   => $dataForm['alamat_instansi'] ?? '',
                    'tanggal_mulai'     => $dataForm['tanggal_mulai'] ?? '',
                    'tanggal_selesai'   => $dataForm['tanggal_selesai'] ?? '',
                    'judul_penelitian'  => $dataForm['judul_penelitian'] ?? '',
                    'bidang_penelitian' => $dataForm['bidang_penelitian'] ?? '',
                    'judul_skripsi'     => $pengajuanJudul?->judul ?? '',
                    'bidang_kajian'     => $pengajuanJudul?->bidang_kajian ?? '',
                    'nama_pembimbing'   => $pembimbing?->nama ?? '',
                    'nip_pembimbing'    => $pembimbing?->nip ?? '',
                    'nama_pembimbing_1' => $pembimbing?->nama ?? '',
                    'nip_pembimbing_1'  => $pembimbing?->nip ?? '',
                    'nama_pembimbing_2' => $pembimbing2?->nama ?? '',
                    'nip_pembimbing_2'  => $pembimbing2?->nip ?? '',
                    'nama_penguji'      => $penguji?->nama ?? '',
                    'nip_penguji'       => $penguji?->nip ?? '',
                    'nama_penguji_1'    => $penguji?->nama ?? '',
                    'nip_penguji_1'     => $penguji?->nip ?? '',
                    'nama_penguji_2'    => $penguji2?->nama ?? '',
                    'nip_penguji_2'     => $penguji2?->nip ?? '',
                    'tanggal_seminar'   => $dataForm['tanggal_rencana'] ?? '',
                    'tanggal_sidang'    => $dataForm['tanggal_rencana'] ?? '',
                    'waktu_sidang'      => $dataForm['waktu_rencana'] ?? '',
                    'tempat_sidang'     => $dataForm['tempat'] ?? '',
                ], fn ($v) => $v !== null && $v !== ''));
            @endphp

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" style="min-height: 297mm;">
                <iframe x-ref="previewFrame"
                        src="{{ route('preview-surat') }}?{{ $previewParams }}"
                        style="width: 100%; min-height: 297mm; border: none;"
                        title="Pratinjau Surat">
                </iframe>
            </div>
            <p class="mt-2 text-center text-xs text-slate-400">Preview langsung dari template Word aktif</p>
        </div>

    </div>

    {{-- Modal Generate --}}
    <div x-show="modalGenerate" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4">
        <div @click.stop class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-brand-100">
                <x-icon name="file-cog" class="h-6 w-6 text-brand-600" />
            </div>
            <h3 class="text-base font-bold text-slate-900">Generate Dokumen Surat</h3>
            <p class="mt-1 text-sm text-slate-500">Nomor urutan yang akan digunakan:</p>
            <p class="mt-2 mb-2 rounded-xl bg-slate-50 px-3 py-2 font-mono text-sm font-bold text-slate-800" x-text="nomorPenuh"></p>
            <p class="mb-5 text-xs text-slate-400">File .docx akan dibuat dari template. Unduh, cetak, dan minta TTD Kaprodi.</p>
            <div class="flex justify-end gap-3">
                <button @click="modalGenerate = false"
                        class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                    Batal
                </button>
                <button @click="modalGenerate = false; document.getElementById('form-generate').submit()"
                        class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600 transition-colors">
                    <x-icon name="file-cog" class="h-4 w-4" />
                    Ya, Generate
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Tolak --}}
    <div x-show="modalTolak" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4">
        <div @click.stop class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-red-100">
                <x-icon name="circle-x" class="h-6 w-6 text-red-600" />
            </div>
            <h3 class="text-base font-bold text-slate-900">Tolak Pengajuan Surat</h3>
            <p class="mt-1 mb-4 text-sm text-slate-500">Berikan alasan penolakan yang jelas untuk mahasiswa.</p>
            <form method="POST" action="{{ route('admin.surat.tolak', $surat) }}" class="space-y-4">
                @csrf
                <textarea name="catatan_penolakan" rows="3" required
                          placeholder="Contoh: Dokumen pendukung tidak lengkap..."
                          class="block w-full rounded-xl border-slate-200 text-sm focus:border-red-400 focus:ring-red-400">{{ old('catatan_penolakan') }}</textarea>
                @error('catatan_penolakan') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                <div class="flex justify-end gap-3">
                    <button type="button" @click="modalTolak = false"
                            class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 transition-colors">
                        <x-icon name="circle-x" class="h-4 w-4" />
                        Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Selesai --}}
    <div x-show="modalSelesai" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4">
        <div @click.stop class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100">
                <x-icon name="flag" class="h-6 w-6 text-emerald-600" />
            </div>
            <h3 class="text-base font-bold text-slate-900">Tandai Selesai</h3>
            <p class="mt-1 mb-6 text-sm text-slate-500">Mahasiswa dapat mengunduh scan surat kapan saja setelah ini.</p>
            <div class="flex justify-end gap-3">
                <button @click="modalSelesai = false"
                        class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                    Batal
                </button>
                <form method="POST" action="{{ route('admin.surat.selesaikan', $surat) }}">
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

</x-app-layout>
