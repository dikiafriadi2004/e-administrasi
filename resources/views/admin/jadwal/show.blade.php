<x-app-layout>
    <x-slot name="title">Detail Jadwal</x-slot>

    <div class="mb-4 flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('admin.jadwal.index') }}" class="hover:text-brand-600 transition-colors">Jadwal</a>
        <x-icon name="chevron-right" class="h-4 w-4 text-slate-300" />
        <span class="text-slate-700 font-medium">{{ $pengajuan->mahasiswa->user->name }}</span>
    </div>

    @php
        $jenisList  = ['seminar_proposal' => 'Seminar Proposal', 'sidang_skripsi' => 'Sidang Skripsi'];
        $jenisLabel = $jenisList[$pengajuan->jenis_surat] ?? $pengajuan->jenis_surat;

        // Preview params
        $previewParams = http_build_query(array_filter([
            'jenis'             => $pengajuan->jenis_surat,
            'nomor_urut'        => $pengajuan->nomor_surat ?? '',
            'nomor_surat'       => $pengajuan->nomor_surat ?? '',
            'nama_mahasiswa'    => $pengajuan->mahasiswa->user->name,
            'nim'               => $pengajuan->mahasiswa->nim,
            'judul_skripsi'     => $pengajuan->pengajuanJudul?->judul ?? '',
            'bidang_kajian'     => $pengajuan->pengajuanJudul?->bidang_kajian ?? '',
            'nama_pembimbing'   => $pengajuan->pengajuanJudul?->dosenPembimbing?->nama ?? '',
            'nip_pembimbing'    => $pengajuan->pengajuanJudul?->dosenPembimbing?->nip ?? '',
            'nama_pembimbing_1' => $pengajuan->pengajuanJudul?->dosenPembimbing?->nama ?? '',
            'nip_pembimbing_1'  => $pengajuan->pengajuanJudul?->dosenPembimbing?->nip ?? '',
            'nama_penguji_1'    => $pengajuan->dosenPenguji?->nama ?? '',
            'nip_penguji_1'     => $pengajuan->dosenPenguji?->nip ?? '',
            'nama_penguji_2'    => $pengajuan->dosenPenguji2?->nama ?? '',
            'nip_penguji_2'     => $pengajuan->dosenPenguji2?->nip ?? '',
            'tanggal_seminar'   => $pengajuan->tanggal_jadwal?->locale('id')->isoFormat('dddd, D MMMM Y') ?? '',
            'tanggal_sidang'    => $pengajuan->tanggal_jadwal?->locale('id')->isoFormat('dddd, D MMMM Y') ?? '',
            'waktu_sidang'      => $pengajuan->waktu_jadwal ?? '',
            'tempat_sidang'     => $pengajuan->tempat_jadwal ?? '',
        ], fn ($v) => $v !== null && $v !== ''));
    @endphp

    {{-- Layout dua kolom: kiri = aksi, kanan = preview --}}
    <div class="flex gap-5 items-start"
         x-data="{
             nomorUrutan: '{{ old('nomor_urutan', $pengajuan->nomor_surat ?? '') }}',
             nomorSuffix: '{{ $nomorSuffix }}',
             get nomorPenuh() { return (this.nomorUrutan || '...') + this.nomorSuffix; }
         }">

        {{-- ===== KOLOM KIRI: Info + Aksi ===== --}}
        <div class="w-96 shrink-0 space-y-4">

            {{-- Info Jadwal --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-3 flex items-start justify-between gap-2">
                    <div>
                        <h2 class="text-sm font-bold text-slate-800">{{ $jenisLabel }}</h2>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $pengajuan->created_at->format('d M Y') }}</p>
                    </div>
                    <x-status-badge :status="$pengajuan->status" />
                </div>

                <dl class="space-y-2 text-xs">
                    <div class="flex gap-2">
                        <dt class="w-24 shrink-0 font-medium text-slate-500">Mahasiswa</dt>
                        <dd class="text-slate-800">{{ $pengajuan->mahasiswa->user->name }}
                            <span class="text-slate-400">({{ $pengajuan->mahasiswa->nim }})</span></dd>
                    </div>
                    <div class="flex gap-2">
                        <dt class="w-24 shrink-0 font-medium text-slate-500">Tanggal</dt>
                        <dd class="font-semibold text-slate-800">
                            {{ \Carbon\Carbon::parse($pengajuan->tanggal_jadwal)->locale('id')->isoFormat('dddd, D MMMM Y') }}
                        </dd>
                    </div>
                    @if ($pengajuan->waktu_jadwal)
                        <div class="flex gap-2">
                            <dt class="w-24 shrink-0 font-medium text-slate-500">Waktu</dt>
                            <dd class="text-slate-800">{{ $pengajuan->waktu_jadwal }}</dd>
                        </div>
                    @endif
                    @if ($pengajuan->tempat_jadwal)
                        <div class="flex gap-2">
                            <dt class="w-24 shrink-0 font-medium text-slate-500">Tempat</dt>
                            <dd class="text-slate-800">{{ $pengajuan->tempat_jadwal }}</dd>
                        </div>
                    @endif
                    @if ($pengajuan->pengajuanJudul)
                        <div class="flex gap-2">
                            <dt class="w-24 shrink-0 font-medium text-slate-500">Judul</dt>
                            <dd class="text-slate-700 leading-snug">{{ $pengajuan->pengajuanJudul->judul }}</dd>
                        </div>
                        <div class="flex gap-2">
                            <dt class="w-24 shrink-0 font-medium text-slate-500">Pembimbing</dt>
                            <dd class="text-slate-800">{{ $pengajuan->pengajuanJudul->dosenPembimbing?->nama ?? '—' }}</dd>
                        </div>
                    @endif
                    @if ($pengajuan->dosenPenguji)
                        <div class="flex gap-2">
                            <dt class="w-24 shrink-0 font-medium text-slate-500">Penguji I</dt>
                            <dd class="text-slate-800">{{ $pengajuan->dosenPenguji->nama }}</dd>
                        </div>
                    @endif
                    @if ($pengajuan->dosenPenguji2)
                        <div class="flex gap-2">
                            <dt class="w-24 shrink-0 font-medium text-slate-500">Penguji II</dt>
                            <dd class="text-slate-800">{{ $pengajuan->dosenPenguji2->nama }}</dd>
                        </div>
                    @endif
                    @if ($pengajuan->nomor_surat)
                        <div class="flex gap-2">
                            <dt class="w-24 shrink-0 font-medium text-slate-500">Nomor Urut</dt>
                            <dd class="font-mono text-slate-800">{{ $pengajuan->nomor_surat }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Berkas Syarat --}}
            @if ($pengajuan->berkas->count())
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <h3 class="mb-2.5 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <x-icon name="paperclip" class="h-3.5 w-3.5" />
                        Berkas Syarat
                    </h3>
                    <ul class="space-y-1.5">
                        @foreach ($pengajuan->berkas as $berkas)
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

            {{-- Panel Verifikasi Berkas (khusus sidang_skripsi, status diajukan) --}}
            @if ($pengajuan->jenis_surat === 'sidang_skripsi' && $pengajuan->status === 'diajukan')
                <div class="rounded-2xl border {{ $pengajuan->berkas_diverifikasi ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50' }} p-4 shadow-sm">
                    <h3 class="mb-1 flex items-center gap-2 text-xs font-semibold {{ $pengajuan->berkas_diverifikasi ? 'text-emerald-800' : 'text-amber-800' }}">
                        <x-icon name="clipboard-check" class="h-4 w-4 {{ $pengajuan->berkas_diverifikasi ? 'text-emerald-500' : 'text-amber-500' }}" />
                        Verifikasi Berkas Sidang
                    </h3>

                    @if ($pengajuan->berkas_diverifikasi)
                        <div class="flex items-center gap-2 mb-2">
                            <x-icon name="circle-check" class="h-4 w-4 text-emerald-600 shrink-0" />
                            <p class="text-xs font-medium text-emerald-700">Berkas sudah diverifikasi — menunggu ACC Kaprodi</p>
                        </div>
                        <details class="text-xs">
                            <summary class="cursor-pointer text-emerald-600 hover:text-emerald-800">Verifikasi ulang</summary>
                            <form method="POST" action="{{ route('admin.jadwal.verifikasi-berkas', $pengajuan) }}" class="mt-2 space-y-2">
                                @csrf
                                <div class="flex gap-3">
                                    <label class="flex items-center gap-1.5 text-xs cursor-pointer">
                                        <input type="radio" name="keputusan" value="lulus" checked class="text-emerald-500" />
                                        <span class="text-emerald-700 font-medium">Berkas Lengkap</span>
                                    </label>
                                    <label class="flex items-center gap-1.5 text-xs cursor-pointer">
                                        <input type="radio" name="keputusan" value="kembalikan" class="text-red-500" />
                                        <span class="text-red-600 font-medium">Kembalikan</span>
                                    </label>
                                </div>
                                <textarea name="catatan" rows="2" placeholder="Catatan untuk mahasiswa (wajib jika dikembalikan)..."
                                          class="block w-full rounded-xl border-slate-200 text-xs focus:border-brand-400 focus:ring-brand-400"></textarea>
                                <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-slate-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-600 transition-colors">
                                    Simpan
                                </button>
                            </form>
                        </details>

                    @else
                        <p class="mb-3 text-[11px] text-amber-700">
                            Periksa kelengkapan berkas sidang mahasiswa. Jika sudah sesuai, klik <strong>Berkas Lengkap</strong> agar bisa diteruskan ke Kaprodi.
                            Jika belum, klik <strong>Kembalikan</strong> dengan catatan apa yang kurang.
                        </p>

                        @if ($pengajuan->catatan_admin)
                            <div class="mb-3 rounded-xl border border-red-100 bg-red-50 px-3 py-2 text-xs text-red-700">
                                <p class="font-medium mb-0.5">Catatan sebelumnya:</p>
                                <p>{{ $pengajuan->catatan_admin }}</p>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('admin.jadwal.verifikasi-berkas', $pengajuan) }}" class="space-y-3">
                            @csrf
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 text-xs cursor-pointer">
                                    <input type="radio" name="keputusan" value="lulus" class="text-emerald-500" required />
                                    <span class="font-medium text-emerald-700">Berkas Lengkap — Teruskan ke Kaprodi</span>
                                </label>
                            </div>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 text-xs cursor-pointer">
                                    <input type="radio" name="keputusan" value="kembalikan" class="text-red-500" />
                                    <span class="font-medium text-red-600">Kembalikan ke Mahasiswa</span>
                                </label>
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-slate-600 mb-1">
                                    Catatan untuk Mahasiswa
                                    <span class="text-slate-400">(wajib jika dikembalikan)</span>
                                </label>
                                <textarea name="catatan" rows="2"
                                          placeholder="Contoh: KRS semester ini belum dilampirkan..."
                                          class="block w-full rounded-xl border-slate-200 text-xs focus:border-brand-400 focus:ring-brand-400"></textarea>
                                @error('catatan') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <button type="submit"
                                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-amber-500 px-3 py-2 text-xs font-semibold text-white hover:bg-amber-600 transition-colors">
                                <x-icon name="clipboard-check" class="h-3.5 w-3.5" />
                                Simpan Hasil Verifikasi
                            </button>
                        </form>
                    @endif
                </div>
            @endif

            {{-- Langkah 0: Tetapkan Jadwal (jika belum ada) --}}
            @if (in_array($pengajuan->status, ['disetujui', 'menunggu_ttd', 'sudah_ditandatangani', 'selesai']))
                <div class="rounded-2xl border {{ $pengajuan->tanggal_jadwal ? 'border-slate-200 bg-white' : 'border-amber-200 bg-amber-50' }} p-4 shadow-sm">
                    <h3 class="mb-1 flex items-center gap-2 text-xs font-semibold {{ $pengajuan->tanggal_jadwal ? 'text-slate-700' : 'text-amber-800' }}">
                        <x-icon name="calendar" class="h-4 w-4 {{ $pengajuan->tanggal_jadwal ? 'text-sky-500' : 'text-amber-500' }}" />
                        {{ $pengajuan->tanggal_jadwal ? 'Jadwal Ditetapkan' : 'Langkah 1 — Tetapkan Jadwal' }}
                    </h3>

                    @if ($pengajuan->tanggal_jadwal)
                        {{-- Jadwal sudah ada: tampilkan + form edit --}}
                        <div class="mb-3 space-y-1 text-xs">
                            <p class="text-slate-700">
                                <span class="font-medium text-slate-500 w-16 inline-block">Tanggal:</span>
                                {{ \Carbon\Carbon::parse($pengajuan->tanggal_jadwal)->locale('id')->isoFormat('dddd, D MMMM Y') }}
                            </p>
                            <p class="text-slate-700">
                                <span class="font-medium text-slate-500 w-16 inline-block">Waktu:</span>
                                {{ $pengajuan->waktu_jadwal }}
                            </p>
                            <p class="text-slate-700">
                                <span class="font-medium text-slate-500 w-16 inline-block">Tempat:</span>
                                {{ $pengajuan->tempat_jadwal }}
                            </p>
                        </div>
                        <details class="text-xs">
                            <summary class="cursor-pointer text-slate-400 hover:text-slate-600">Ubah jadwal</summary>
                            <form method="POST" action="{{ route('admin.jadwal.tetapkan-jadwal', $pengajuan) }}" class="mt-2 space-y-2">
                                @csrf
                                <input type="date" name="tanggal_jadwal"
                                       value="{{ $pengajuan->tanggal_jadwal?->format('Y-m-d') }}"
                                       class="block w-full rounded-xl border-slate-200 text-xs shadow-sm focus:border-brand-400 focus:ring-brand-400" required />
                                <input type="text" name="waktu_jadwal"
                                       value="{{ $pengajuan->waktu_jadwal }}"
                                       placeholder="09.00 s/d selesai"
                                       class="block w-full rounded-xl border-slate-200 text-xs shadow-sm focus:border-brand-400 focus:ring-brand-400" required />
                                <input type="text" name="tempat_jadwal"
                                       value="{{ $pengajuan->tempat_jadwal }}"
                                       placeholder="Ruang 01.03"
                                       class="block w-full rounded-xl border-slate-200 text-xs shadow-sm focus:border-brand-400 focus:ring-brand-400" required />
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 rounded-xl bg-sky-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-sky-600 transition-colors">
                                    <x-icon name="save" class="h-3.5 w-3.5" />
                                    Simpan Perubahan
                                </button>
                            </form>
                        </details>

                    @else
                        {{-- Jadwal belum ada: form wajib diisi dulu sebelum generate --}}
                        <p class="mb-3 text-[11px] text-amber-700">Tentukan tanggal, waktu, dan tempat sebelum membuat surat undangan.</p>
                        <form method="POST" action="{{ route('admin.jadwal.tetapkan-jadwal', $pengajuan) }}" class="space-y-2">
                            @csrf
                            <div>
                                <label class="block text-[11px] font-medium text-slate-600 mb-1">Tanggal <span class="text-red-500">*</span></label>
                                <input type="date" name="tanggal_jadwal"
                                       class="block w-full rounded-xl border-slate-200 text-xs shadow-sm focus:border-brand-400 focus:ring-brand-400" required />
                                @error('tanggal_jadwal') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-slate-600 mb-1">Waktu <span class="text-red-500">*</span></label>
                                <input type="text" name="waktu_jadwal"
                                       placeholder="09.00 s/d selesai"
                                       class="block w-full rounded-xl border-slate-200 text-xs shadow-sm focus:border-brand-400 focus:ring-brand-400" required />
                                @error('waktu_jadwal') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-slate-600 mb-1">Tempat / Ruangan <span class="text-red-500">*</span></label>
                                <input type="text" name="tempat_jadwal"
                                       placeholder="Ruang 01.03"
                                       class="block w-full rounded-xl border-slate-200 text-xs shadow-sm focus:border-brand-400 focus:ring-brand-400" required />
                                @error('tempat_jadwal') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <button type="submit"
                                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-sky-500 px-3 py-2 text-xs font-semibold text-white hover:bg-sky-600 transition-colors">
                                <x-icon name="calendar-check" class="h-3.5 w-3.5" />
                                Tetapkan Jadwal
                            </button>
                        </form>
                    @endif
                </div>
            @endif

            {{-- Langkah 1 (sekarang Langkah 2): Generate DOCX — hanya aktif jika jadwal sudah ada --}}
            @if (in_array($pengajuan->status, ['disetujui', 'menunggu_ttd', 'sudah_ditandatangani', 'selesai']))
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <h3 class="mb-1 flex items-center gap-2 text-xs font-semibold text-slate-700">
                        <x-icon name="file-cog" class="h-4 w-4 text-brand-500" />
                        Langkah 2 — Generate Surat Undangan (DOCX)
                    </h3>
                    <p class="mb-3 text-[11px] text-slate-400">Isi nomor urut, generate, cetak, minta TTD Kaprodi.</p>

                    @if (! $pengajuan->tanggal_jadwal)
                        <div class="rounded-xl border border-amber-100 bg-amber-50 px-3 py-2 text-xs text-amber-700">
                            <x-icon name="alert-triangle" class="h-3.5 w-3.5 inline mr-1" />
                            Tetapkan jadwal terlebih dahulu sebelum generate surat undangan.
                        </div>
                    @else

                    @if ($pengajuan->file_docx)
                        <div class="mb-3 flex items-center gap-2 rounded-xl border border-brand-100 bg-brand-50 px-3 py-2">
                            <x-icon name="check-circle" class="h-4 w-4 shrink-0 text-brand-500" />
                            <p class="flex-1 text-xs text-brand-700">
                                Sudah digenerate (nomor: <strong>{{ $pengajuan->nomor_surat }}</strong>)
                            </p>
                            <a href="{{ route('admin.jadwal.download-undangan', $pengajuan) }}"
                               class="inline-flex items-center gap-1 rounded-lg border border-brand-300 bg-white px-2 py-1 text-xs font-medium text-brand-700 hover:bg-brand-50 transition-colors">
                                <x-icon name="download" class="h-3 w-3" />
                                DOCX
                            </a>
                        </div>
                        <p class="mb-2 text-[11px] text-slate-400">Generate ulang:</p>
                    @endif

                    <form method="POST" action="{{ route('admin.jadwal.generate-undangan', $pengajuan) }}" class="space-y-2">
                        @csrf
                        <div class="flex items-center gap-0">
                            <input type="text" name="nomor_urutan"
                                   x-model="nomorUrutan"
                                   value="{{ old('nomor_urutan', $pengajuan->nomor_surat ?? '') }}"
                                   placeholder="2032"
                                   class="w-20 rounded-l-xl border border-r-0 border-slate-200 bg-white px-2 py-2 text-xs font-mono shadow-sm focus:border-brand-400 focus:ring-brand-400"
                                   required />
                            <span class="flex-1 truncate rounded-r-xl border border-slate-200 bg-slate-50 px-2 py-2 text-[10px] font-mono text-slate-400"
                                  x-text="nomorSuffix"></span>
                            <button type="submit"
                                    class="ml-2 inline-flex items-center gap-1 rounded-xl bg-brand-500 px-3 py-2 text-xs font-semibold text-white hover:bg-brand-600 transition-colors">
                                <x-icon name="file-cog" class="h-3.5 w-3.5" />
                                Generate
                            </button>
                        </div>
                        <p class="text-[10px] text-slate-400">
                            Preview: <span class="font-mono text-brand-600" x-text="nomorPenuh"></span>
                        </p>
                        @error('nomor_urutan') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </form>
                    @endif {{-- end guard tanggal_jadwal --}}
                </div>
            @endif

            {{-- Langkah 3: Upload Scan TTD --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <h3 class="mb-1 flex items-center gap-2 text-xs font-semibold text-slate-700">
                    <x-icon name="upload" class="h-4 w-4 text-brand-500" />
                    Langkah 3 — Upload Scan TTD
                </h3>
                <p class="mb-3 text-[11px] text-slate-400">
                    Upload scan surat yang sudah ditandatangani Kaprodi. Mahasiswa dapat mendownloadnya.
                </p>

                @if ($pengajuan->file_scan)
                    <div class="mb-3 flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2">
                        <div class="flex items-center gap-2">
                            <x-icon name="circle-check" class="h-4 w-4 shrink-0 text-emerald-600" />
                            <span class="text-xs font-medium text-emerald-800">Sudah diupload — mahasiswa bisa download</span>
                        </div>
                        <a href="{{ route('admin.jadwal.download-undangan', $pengajuan) }}"
                           class="inline-flex items-center gap-1 rounded-lg border border-emerald-300 bg-white px-2 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-50 transition-colors">
                            <x-icon name="download" class="h-3 w-3" />
                            PDF
                        </a>
                    </div>
                    <p class="mb-2 text-[11px] text-slate-400">Upload ulang jika ada revisi:</p>
                @endif

                <form method="POST" action="{{ route('admin.jadwal.upload-undangan', $pengajuan) }}"
                      enctype="multipart/form-data" class="space-y-2">
                    @csrf
                    <input type="file" name="file_undangan" accept=".pdf"
                           {{ $pengajuan->file_scan ? '' : 'required' }}
                           class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs
                                  file:mr-2 file:rounded-lg file:border-0 file:bg-brand-50 file:px-2.5 file:py-1
                                  file:text-xs file:font-medium file:text-brand-700 hover:file:bg-brand-100" />
                    <p class="text-[10px] text-slate-400">Format PDF · Maks 10 MB</p>
                    @error('file_undangan') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-3 py-2 text-xs font-semibold text-white hover:bg-brand-600 transition-colors">
                        <x-icon name="upload" class="h-3.5 w-3.5" />
                        {{ $pengajuan->file_scan ? 'Upload Ulang' : 'Upload Surat Undangan' }}
                    </button>
                </form>
            </div>

            {{-- Langkah 3: Upload Absensi Seminar (khusus seminar_proposal) --}}
            @if ($pengajuan->jenis_surat === 'seminar_proposal')
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <h3 class="mb-1 flex items-center gap-2 text-xs font-semibold text-slate-700">
                        <x-icon name="clipboard-list" class="h-4 w-4 text-sky-500" />
                        Langkah 3 — Upload Absensi Seminar
                    </h3>
                    <p class="mb-3 text-[11px] text-slate-400">
                        Upload absensi kehadiran seminar proposal. Mahasiswa akan melihat file ini sebagai syarat pengajuan Izin Penelitian.
                    </p>

                    @if ($pengajuan->file_absensi_seminar)
                        <div class="mb-3 flex items-center justify-between rounded-xl border border-sky-200 bg-sky-50 px-3 py-2">
                            <div class="flex items-center gap-2">
                                <x-icon name="circle-check" class="h-4 w-4 shrink-0 text-sky-600" />
                                <span class="text-xs font-medium text-sky-800">Absensi sudah diupload</span>
                            </div>
                            <a href="{{ route('admin.jadwal.download-absensi', $pengajuan) }}"
                               class="inline-flex items-center gap-1 rounded-lg border border-sky-300 bg-white px-2 py-1 text-xs font-medium text-sky-700 hover:bg-sky-50 transition-colors">
                                <x-icon name="download" class="h-3 w-3" />
                                Download
                            </a>
                        </div>
                        <p class="mb-2 text-[11px] text-slate-400">Upload ulang jika ada revisi:</p>
                    @endif

                    <form method="POST" action="{{ route('admin.jadwal.upload-absensi', $pengajuan) }}"
                          enctype="multipart/form-data" class="space-y-2">
                        @csrf
                        <input type="file" name="file_absensi" accept=".pdf,.jpg,.jpeg,.png"
                               {{ $pengajuan->file_absensi_seminar ? '' : 'required' }}
                               class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs
                                      file:mr-2 file:rounded-lg file:border-0 file:bg-sky-50 file:px-2.5 file:py-1
                                      file:text-xs file:font-medium file:text-sky-700 hover:file:bg-sky-100" />
                        <p class="text-[10px] text-slate-400">Format PDF / JPG / PNG · Maks 10 MB</p>
                        @error('file_absensi') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-xl bg-sky-500 px-3 py-2 text-xs font-semibold text-white hover:bg-sky-600 transition-colors">
                            <x-icon name="upload" class="h-3.5 w-3.5" />
                            {{ $pengajuan->file_absensi_seminar ? 'Upload Ulang Absensi' : 'Upload Absensi Seminar' }}
                        </button>
                    </form>
                </div>
            @endif

            {{-- Riwayat Status --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <h3 class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <x-icon name="history" class="h-3.5 w-3.5" />
                    Riwayat Status
                </h3>
                <ol class="relative ml-2 space-y-3 border-l border-slate-200">
                    @forelse ($pengajuan->statusHistories as $h)
                        <li class="ml-4">
                            <div class="absolute -left-1.5 mt-1.5 h-3 w-3 rounded-full border-2 border-white bg-brand-400 shadow-sm"></div>
                            <p class="text-[10px] text-slate-400">
                                {{ $h->created_at?->format('d M Y, H:i') }}
                                @if ($h->changedBy) — {{ $h->changedBy->name }} @endif
                            </p>
                            <p class="text-xs font-medium text-slate-700">→ {{ $h->status_baru }}</p>
                            @if ($h->catatan)
                                <p class="text-xs text-slate-500 mt-0.5">{{ $h->catatan }}</p>
                            @endif
                        </li>
                    @empty
                        <li class="ml-4 text-xs text-slate-400">Belum ada riwayat.</li>
                    @endforelse
                </ol>
            </div>

        </div>

        {{-- ===== KOLOM KANAN: Preview Surat ===== --}}
        <div class="flex-1 min-w-0">
            <div class="mb-2 flex items-center justify-between">
                <p class="flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-slate-500">
                    <x-icon name="eye" class="h-3.5 w-3.5" />
                    Pratinjau Surat
                </p>
                <p class="text-xs text-slate-400">Preview dari template aktif</p>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" style="min-height: 297mm;">
                <iframe src="{{ route('preview-surat') }}?{{ $previewParams }}"
                        style="width: 100%; min-height: 297mm; border: none;"
                        title="Pratinjau Surat">
                </iframe>
            </div>

            @if ($pengajuan->file_scan)
                <div class="mt-3 flex items-center justify-between rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                    <div class="flex items-center gap-2">
                        <x-icon name="circle-check" class="h-5 w-5 text-emerald-600 shrink-0" />
                        <div>
                            <p class="text-sm font-semibold text-emerald-800">Surat sudah tersedia untuk mahasiswa</p>
                            <p class="text-xs text-emerald-600">Mahasiswa dapat mendownload surat undangan ini dari dashboard.</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.jadwal.download-undangan', $pengajuan) }}"
                       class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-300 bg-white px-3 py-2 text-xs font-medium text-emerald-700 hover:bg-emerald-50 transition-colors shrink-0">
                        <x-icon name="download" class="h-3.5 w-3.5" />
                        Download Scan PDF
                    </a>
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
