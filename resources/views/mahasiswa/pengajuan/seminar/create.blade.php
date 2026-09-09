<x-app-layout>
    <x-slot name="title">Ajukan Seminar Proposal</x-slot>
    <div class="mb-4 flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('mahasiswa.riwayat.index') }}" class="hover:text-brand-600">Riwayat</a>
        <span>/</span>
        <span class="text-gray-700">Seminar Proposal</span>
    </div>
    <div class="max-w-2xl mx-auto">
        <div class="rounded-xl border bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold text-slate-800">Pengajuan Seminar Proposal</h2>

            {{-- Data auto-terisi dari judul --}}
            <div class="mb-4 rounded-xl border border-brand-100 bg-brand-50 px-4 py-3 text-xs space-y-1.5">
                <p class="font-semibold text-brand-700 mb-1">Data dari Judul yang Disetujui</p>
                <div class="flex gap-2">
                    <span class="w-24 shrink-0 text-slate-500">Judul:</span>
                    <span class="text-slate-800 break-words leading-snug">{{ $pengajuanJudul->judul }}</span>
                </div>
                <div class="flex gap-2">
                    <span class="w-24 shrink-0 text-slate-500">Pembimbing:</span>
                    <span class="text-slate-800">{{ $pengajuanJudul->dosenPembimbing?->nama ?? '—' }}</span>
                </div>
            </div>

            {{-- Info jadwal --}}
            <div class="mb-5 rounded-xl border border-sky-100 bg-sky-50 px-4 py-3 text-xs">
                <div class="flex items-start gap-2">
                    <x-icon name="info" class="h-3.5 w-3.5 shrink-0 text-sky-500 mt-0.5" />
                    <p class="text-sky-800">
                        Jadwal seminar (tanggal, waktu, tempat, penguji) sepenuhnya ditetapkan oleh
                        <strong>Kaprodi dan Admin</strong>. Upload kedua berkas di bawah lalu klik kirim.
                    </p>
                </div>
            </div>

            @if ($errors->any())
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <p class="font-semibold mb-1">Periksa isian berikut:</p>
                    <ul class="list-disc list-inside space-y-0.5 text-xs">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('mahasiswa.pengajuan.seminar.store') }}"
                  enctype="multipart/form-data" class="space-y-4">
                @csrf

                {{-- Berkas 1: Cover ACC Pembimbing (wajib) --}}
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <label for="fileCover" class="block text-xs font-semibold text-slate-700 mb-1">
                        Cover Proposal yang Telah di-ACC Dosen Pembimbing
                        <span class="text-red-500 ml-1">*</span>
                    </label>
                    <p class="mb-2 text-xs text-slate-500">
                        Upload cover proposal yang sudah disetujui dan ditandatangani oleh dosen pembimbing.
                    </p>
                    <input id="fileCover" name="fileCover" type="file" accept=".pdf,.doc,.docx" required
                           class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm
                                  file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1
                                  file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100" />
                    <p class="mt-1 text-xs text-slate-400">PDF, DOC, atau DOCX · Maks 10 MB</p>
                    @error('fileCover') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Berkas 2: Riwayat Bimbingan (wajib) --}}
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <label for="fileRiwayatBimbingan" class="block text-xs font-semibold text-slate-700 mb-1">
                        PDF Riwayat Bimbingan Proposal (TTD Dosen Pembimbing)
                        <span class="text-red-500 ml-1">*</span>
                    </label>
                    <p class="mb-2 text-xs text-slate-500">
                        Upload dokumen riwayat bimbingan proposal yang sudah ditandatangani oleh dosen pembimbing.
                    </p>
                    <input id="fileRiwayatBimbingan" name="fileRiwayatBimbingan" type="file" accept=".pdf,.doc,.docx" required
                           class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm
                                  file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1
                                  file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100" />
                    <p class="mt-1 text-xs text-slate-400">PDF, DOC, atau DOCX · Maks 10 MB</p>
                    @error('fileRiwayatBimbingan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Berkas tambahan opsional --}}
                <div>
                    <label for="fileBerkas" class="block text-xs font-medium text-slate-700 mb-1">
                        Berkas Tambahan
                        <span class="text-slate-400">(opsional, bisa beberapa)</span>
                    </label>
                    <input id="fileBerkas" name="fileBerkas[]" type="file" multiple accept=".pdf,.doc,.docx"
                           class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm
                                  file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1
                                  file:text-sm file:font-medium file:text-slate-600 hover:file:bg-slate-200" />
                    <p class="mt-1 text-xs text-slate-400">KRS, dokumen lainnya · PDF/DOC/DOCX · Maks 10 MB per file</p>
                    @error('fileBerkas.*') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex flex-col gap-2 pt-2">
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600 transition-colors">
                        Kirim Pengajuan
                    </button>
                    <a href="{{ route('mahasiswa.riwayat.index') }}"
                       class="w-full text-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
