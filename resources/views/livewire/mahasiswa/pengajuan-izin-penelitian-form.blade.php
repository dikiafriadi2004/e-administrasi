<div>
    <div class="max-w-2xl mx-auto">

        {{-- ===== KIRI: Form Input ===== --}}
        <div>

            {{-- Guard: seminar belum selesai --}}
            @if (! $this->seminarSelesai)
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                    <div class="flex items-start gap-3">
                        <x-icon name="lock" class="h-5 w-5 shrink-0 text-amber-500 mt-0.5" />
                        <div>
                            <p class="text-sm font-semibold text-amber-800">Belum Bisa Diajukan</p>
                            <p class="mt-1 text-xs text-amber-700">
                                Seminar Proposal Anda harus disetujui Kaprodi terlebih dahulu sebelum bisa mengajukan Izin Penelitian.
                            </p>
                            <a href="{{ route('mahasiswa.riwayat.index') }}"
                               class="mt-2 inline-flex items-center gap-1.5 text-xs font-medium text-amber-700 underline hover:text-amber-900">
                                Lihat Riwayat Pengajuan
                            </a>
                        </div>
                    </div>
                </div>

            {{-- Guard: seminar selesai tapi absensi belum diupload admin --}}
            @elseif (! $this->bisaAjukan)
                <div class="rounded-xl border border-sky-200 bg-sky-50 p-4">
                    <div class="flex items-start gap-3">
                        <x-icon name="clock" class="h-5 w-5 shrink-0 text-sky-500 mt-0.5" />
                        <div>
                            <p class="text-sm font-semibold text-sky-800">Menunggu Absensi dari Admin</p>
                            <p class="mt-1 text-xs text-sky-700">
                                Seminar Proposal Anda sudah selesai. Admin sedang menyiapkan file absensi seminar.
                                Setelah absensi tersedia, Anda bisa mengajukan Izin Penelitian.
                            </p>
                        </div>
                    </div>
                </div>

            {{-- Guard: sudah punya izin aktif --}}
            @elseif ($this->izinAktif)
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                    <div class="flex items-start gap-3">
                        <x-icon name="check-circle" class="h-5 w-5 shrink-0 text-emerald-500 mt-0.5" />
                        <div>
                            <p class="text-sm font-semibold text-emerald-800">Sudah Ada Pengajuan Aktif</p>
                            <p class="mt-1 text-xs text-emerald-700">
                                Anda sudah memiliki pengajuan izin penelitian dengan status
                                <strong>{{ $this->izinAktif->status }}</strong>.
                            </p>
                            <a href="{{ route('mahasiswa.riwayat.index') }}"
                               class="mt-2 inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 underline hover:text-emerald-900">
                                Lihat Riwayat
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Panel Absensi Seminar (tampil jika seminar selesai) --}}
            @if ($this->seminarSelesai)
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <h3 class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <x-icon name="clipboard-list" class="h-3.5 w-3.5" />
                        Absensi Seminar Proposal
                    </h3>
                    @if ($this->seminarSelesai->file_absensi_seminar)
                        <div class="flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2">
                            <div class="flex items-center gap-2">
                                <x-icon name="file-check" class="h-4 w-4 shrink-0 text-emerald-600" />
                                <span class="text-xs font-medium text-emerald-800">Absensi tersedia</span>
                            </div>
                            <a href="{{ route('mahasiswa.seminar.download-absensi', $this->seminarSelesai) }}"
                               class="inline-flex items-center gap-1 rounded-lg border border-emerald-300 bg-white px-2.5 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-50 transition-colors">
                                <x-icon name="download" class="h-3 w-3" />
                                Download
                            </a>
                        </div>
                    @else
                        <div class="flex items-center gap-2 rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                            <x-icon name="clock" class="h-4 w-4 shrink-0 text-slate-400" />
                            <span class="text-xs text-slate-500">Belum tersedia — admin sedang menyiapkan</span>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Form — hanya tampil jika bisa ajukan dan belum ada izin aktif --}}
            @if ($this->bisaAjukan && ! $this->izinAktif)
                <div class="rounded-xl border bg-white p-5 shadow-sm">
                    <h2 class="mb-4 text-sm font-semibold text-slate-800">Form Izin Penelitian</h2>

                    @error('form')
                        <div class="mb-3 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">
                            {{ $message }}
                        </div>
                    @enderror

                    <form wire:submit="submit" class="space-y-4">

                        {{-- Info mahasiswa (read-only) --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">Nama</label>
                                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700">
                                    {{ auth()->user()?->name ?? '—' }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">NIM</label>
                                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-mono text-slate-700">
                                    {{ auth()->user()?->mahasiswa?->nim ?? '—' }}
                                </div>
                            </div>
                        </div>

                        {{-- Judul (auto-fill, read-only) --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">
                                Judul Penelitian / Skripsi
                                <span class="text-slate-400 font-normal">(dari pengajuan judul)</span>
                            </label>
                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700 leading-relaxed">
                                {{ $judulPenelitian ?: '—' }}
                            </div>
                        </div>

                        {{-- Bidang kajian (auto-fill, read-only) --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Bidang Kajian</label>
                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700">
                                {{ $bidangPenelitian ?: '—' }}
                            </div>
                        </div>

                        {{-- Pembimbing (auto-fill, read-only) --}}
                        @if ($namaPembimbing)
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">Dosen Pembimbing</label>
                                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700">
                                    {{ $namaPembimbing }}
                                </div>
                            </div>
                        @endif

                        {{-- Nama Instansi --}}
                        <div>
                            <label for="namaInstansi" class="block text-xs font-medium text-slate-700 mb-1">
                                Nama Instansi / Lokasi Penelitian <span class="text-red-500">*</span>
                            </label>
                            <input id="namaInstansi" type="text"
                                   wire:model.blur="namaInstansi"
                                   placeholder="Contoh: Dinas Kesehatan Kota X"
                                   class="block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400" />
                            @error('namaInstansi') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Alamat Instansi --}}
                        <div>
                            <label for="alamatInstansi" class="block text-xs font-medium text-slate-700 mb-1">
                                Alamat Lengkap Instansi <span class="text-red-500">*</span>
                            </label>
                            <textarea id="alamatInstansi"
                                      wire:model.blur="alamatInstansi"
                                      rows="2"
                                      placeholder="Jl. Contoh No. 123, Kota"
                                      class="block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400"></textarea>
                            @error('alamatInstansi') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Tanggal Penelitian --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="tanggalMulai" class="block text-xs font-medium text-slate-700 mb-1">
                                    Mulai <span class="text-red-500">*</span>
                                </label>
                                <input id="tanggalMulai" type="date"
                                       wire:model.blur="tanggalMulai"
                                       class="block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400" />
                                @error('tanggalMulai') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="tanggalSelesai" class="block text-xs font-medium text-slate-700 mb-1">
                                    Selesai <span class="text-red-500">*</span>
                                </label>
                                <input id="tanggalSelesai" type="date"
                                       wire:model.blur="tanggalSelesai"
                                       class="block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400" />
                                @error('tanggalSelesai') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Upload Cover Proposal (wajib) --}}
                        <div>
                            <label for="fileCoverProposal" class="block text-xs font-medium text-slate-700 mb-1">
                                Cover Proposal Revisi <span class="text-red-500">*</span>
                            </label>
                            <p class="mb-1.5 text-xs text-slate-400">
                                Upload cover proposal yang sudah direvisi dan ditandatangani dosen pembimbing.
                            </p>
                            <input id="fileCoverProposal" type="file"
                                   wire:model="fileCoverProposal"
                                   accept=".pdf"
                                   class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700
                                          file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-2.5 file:py-1
                                          file:text-xs file:font-medium file:text-brand-700 focus:outline-none" />
                            <p class="mt-1 text-xs text-slate-400">Format PDF · Maks 10 MB</p>
                            @error('fileCoverProposal') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Tombol --}}
                        <div class="flex flex-col gap-2 pt-2">
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-60 cursor-not-allowed"
                                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white transition-opacity hover:bg-brand-600">
                                <svg wire:loading class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span wire:loading.remove>Ajukan Izin Penelitian</span>
                                <span wire:loading>Mengirim...</span>
                            </button>
                            <a href="{{ route('mahasiswa.riwayat.index') }}"
                               class="w-full text-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            @endif

        </div>
    </div>
</div>
