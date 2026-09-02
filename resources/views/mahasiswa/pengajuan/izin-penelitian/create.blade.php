<x-app-layout>
    <x-slot name="title">Ajukan Izin Penelitian</x-slot>
    <div class="mb-4 flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('mahasiswa.riwayat.index') }}" class="hover:text-brand-600">Riwayat</a>
        <span>/</span><span class="text-gray-700">Izin Penelitian</span>
    </div>

    @php
        $mahasiswa = auth()->user()->mahasiswa;
        $seminar = \App\Models\PengajuanSurat::where('mahasiswa_id', $mahasiswa->id)
            ->where('jenis_surat', 'seminar_proposal')
            ->whereIn('status', ['disetujui', 'menunggu_ttd', 'sudah_ditandatangani', 'selesai'])
            ->latest()->first();
        $bisaAjukan = $seminar && $seminar->file_absensi_seminar;
        $izinAktif = \App\Models\PengajuanSurat::where('mahasiswa_id', $mahasiswa->id)
            ->where('jenis_surat', 'izin_penelitian')->whereNotIn('status', ['ditolak'])->exists();
        $judul = \App\Models\PengajuanJudul::where('mahasiswa_id', $mahasiswa->id)->where('status', 'disetujui')->first();
    @endphp

    <div class="max-w-2xl mx-auto space-y-4">

        @if (! $seminar)
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-5">
                <p class="text-sm font-semibold text-amber-800">Belum Bisa Diajukan</p>
                <p class="mt-1 text-xs text-amber-700">Seminar Proposal harus disetujui Kaprodi terlebih dahulu.</p>
            </div>
        @elseif (! $bisaAjukan)
            <div class="rounded-xl border border-sky-200 bg-sky-50 p-5">
                <p class="text-sm font-semibold text-sky-800">Menunggu Absensi dari Admin</p>
                <p class="mt-1 text-xs text-sky-700">Admin sedang menyiapkan file absensi seminar. Tunggu hingga tersedia.</p>
            </div>
        @elseif ($izinAktif)
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5">
                <p class="text-sm font-semibold text-emerald-800">Sudah Ada Pengajuan Aktif</p>
                <a href="{{ route('mahasiswa.riwayat.index') }}" class="mt-1 inline-block text-xs text-emerald-700 underline">Lihat Riwayat</a>
            </div>
        @endif

        {{-- Download absensi --}}
        @if ($seminar)
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="mb-3 text-sm font-semibold text-slate-700">Absensi Seminar Proposal</h3>
                @if ($seminar->file_absensi_seminar)
                    <div class="flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                        <div class="flex items-center gap-2">
                            <x-icon name="file-check" class="h-4 w-4 text-emerald-600" />
                            <p class="text-xs font-medium text-emerald-800">Absensi tersedia</p>
                        </div>
                        <a href="{{ route('mahasiswa.seminar.download-absensi', $seminar) }}"
                           class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-300 bg-white px-3 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-50 transition-colors">
                            <x-icon name="download" class="h-3.5 w-3.5" />
                            Download Absensi
                        </a>
                    </div>
                @else
                    <p class="text-xs text-slate-500">Belum tersedia — admin sedang menyiapkan.</p>
                @endif
            </div>
        @endif

        {{-- Form upload cover proposal --}}
        @if ($bisaAjukan && ! $izinAktif)
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="mb-1 text-sm font-semibold text-slate-800">Ajukan Izin Penelitian</h2>
                <p class="mb-4 text-xs text-slate-500">Upload cover proposal yang sudah direvisi dan ditandatangani oleh <strong>dosen pembimbing dan dosen penguji</strong>.</p>

                @if (session('error'))
                    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
                @endif

                <form method="POST" action="{{ route('mahasiswa.pengajuan.izin-penelitian.store') }}"
                      enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div class="rounded-xl bg-slate-50 px-4 py-3 text-xs space-y-1.5">
                        <div class="flex gap-3"><span class="w-16 text-slate-500">Nama</span><span class="text-slate-800">{{ auth()->user()->name }}</span></div>
                        <div class="flex gap-3"><span class="w-16 text-slate-500">NIM</span><span class="font-mono text-slate-800">{{ $mahasiswa->nim }}</span></div>
                        @if ($judul)
                            <div class="flex gap-3"><span class="w-16 text-slate-500">Judul</span><span class="text-slate-800 leading-snug">{{ $judul->judul }}</span></div>
                        @endif
                    </div>

                    <div>
                        <label for="fileCoverProposal" class="block text-xs font-semibold text-slate-700 mb-1">
                            Cover Proposal <span class="text-red-500">*</span>
                        </label>
                        <input id="fileCoverProposal" name="fileCoverProposal" type="file" accept=".pdf" required
                               class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm
                                      file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1
                                      file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100" />
                        <p class="mt-1 text-xs text-slate-400">Format PDF · Maks 10 MB</p>
                        @error('fileCoverProposal') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex flex-col gap-2 pt-1">
                        <button type="submit" class="w-full inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600 transition-colors">
                            Ajukan Izin Penelitian
                        </button>
                        <a href="{{ route('mahasiswa.riwayat.index') }}" class="w-full text-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors">Batal</a>
                    </div>
                </form>
            </div>
        @endif
    </div>
</x-app-layout>
