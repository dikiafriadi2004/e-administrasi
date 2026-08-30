<div>
    <div class="max-w-2xl mx-auto space-y-4">

        {{-- Guard: seminar belum selesai --}}
        @if (! $this->seminarSelesai)
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-5">
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

        {{-- Guard: seminar selesai tapi absensi belum ada --}}
        @elseif (! $this->bisaAjukan)
            <div class="rounded-xl border border-sky-200 bg-sky-50 p-5">
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
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5">
                <div class="flex items-start gap-3">
                    <x-icon name="check-circle" class="h-5 w-5 shrink-0 text-emerald-500 mt-0.5" />
                    <div>
                        <p class="text-sm font-semibold text-emerald-800">Sudah Ada Pengajuan Aktif</p>
                        <p class="mt-1 text-xs text-emerald-700">
                            Pengajuan izin penelitian Anda sudah ada dengan status
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

        {{-- Panel: Download Absensi Seminar --}}
        @if ($this->seminarSelesai)
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold text-slate-700">
                    <x-icon name="clipboard-list" class="h-4 w-4 text-sky-500" />
                    Absensi Seminar Proposal
                </h3>

                @if ($this->seminarSelesai->file_absensi_seminar)
                    <div class="flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                        <div class="flex items-center gap-2">
                            <x-icon name="file-check" class="h-4 w-4 shrink-0 text-emerald-600" />
                            <div>
                                <p class="text-xs font-semibold text-emerald-800">Absensi tersedia</p>
                                <p class="text-[10px] text-emerald-600">Download untuk keperluan pengajuan izin penelitian</p>
                            </div>
                        </div>
                        <a href="{{ route('mahasiswa.seminar.download-absensi', $this->seminarSelesai) }}"
                           class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-300 bg-white px-3 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-50 transition-colors">
                            <x-icon name="download" class="h-3.5 w-3.5" />
                            Download Absensi
                        </a>
                    </div>
                @else
                    <div class="flex items-center gap-2 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                        <x-icon name="clock" class="h-4 w-4 shrink-0 text-slate-400" />
                        <span class="text-xs text-slate-500">Belum tersedia — admin sedang menyiapkan file absensi</span>
                    </div>
                @endif
            </div>
        @endif

        {{-- Form Pengajuan — hanya tampil jika bisa ajukan --}}
        @if ($this->bisaAjukan && ! $this->izinAktif)
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="mb-1 text-sm font-semibold text-slate-800">Ajukan Izin Penelitian</h2>
                <p class="mb-4 text-xs text-slate-500">
                    Upload cover proposal yang sudah direvisi dan ditandatangani oleh
                    <strong>dosen pembimbing dan dosen penguji</strong>, lalu klik tombol ajukan.
                </p>

                @error('form')
                    <div class="mb-3 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">
                        {{ $message }}
                    </div>
                @enderror

                <form wire:submit="submit" class="space-y-4">

                    {{-- Info mahasiswa --}}
                    <div class="rounded-xl bg-slate-50 px-4 py-3 text-xs space-y-1.5">
                        <div class="flex gap-3">
                            <span class="w-24 text-slate-500">Nama</span>
                            <span class="font-medium text-slate-800">{{ auth()->user()?->name }}</span>
                        </div>
                        <div class="flex gap-3">
                            <span class="w-24 text-slate-500">NIM</span>
                            <span class="font-mono text-slate-800">{{ auth()->user()?->mahasiswa?->nim }}</span>
                        </div>
                        @php
                            $judul = \App\Models\PengajuanJudul::where('mahasiswa_id', auth()->user()->mahasiswa?->id)
                                ->where('status', 'disetujui')->first();
                        @endphp
                        @if ($judul)
                            <div class="flex gap-3">
                                <span class="w-24 text-slate-500">Judul</span>
                                <span class="text-slate-800 leading-snug">{{ $judul->judul }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Upload Cover Proposal --}}
                    <div>
                        <label for="fileCoverProposal" class="block text-xs font-semibold text-slate-700 mb-1">
                            Cover Proposal <span class="text-red-500">*</span>
                        </label>
                        <p class="mb-2 text-xs text-slate-400">
                            Upload cover proposal yang sudah direvisi dan ditandatangani dosen pembimbing <strong>dan</strong> dosen penguji. Format PDF, maks 10 MB.
                        </p>
                        <input id="fileCoverProposal" type="file"
                               wire:model="fileCoverProposal"
                               accept=".pdf"
                               class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700
                                      file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-2.5 file:py-1
                                      file:text-xs file:font-medium file:text-brand-700 focus:outline-none" />
                        @error('fileCoverProposal') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        <div wire:loading wire:target="fileCoverProposal" class="mt-1 text-xs text-brand-600">Mengupload...</div>
                    </div>

                    {{-- Tombol --}}
                    <div class="flex flex-col gap-2 pt-1">
                        <button type="submit"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-60 cursor-not-allowed"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600 transition-colors">
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
