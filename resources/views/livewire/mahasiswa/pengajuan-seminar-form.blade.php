<div>
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
            <div class="mb-4 rounded-xl border border-sky-100 bg-sky-50 px-4 py-3 text-xs">
                <div class="flex items-start gap-2">
                    <x-icon name="info" class="h-3.5 w-3.5 shrink-0 text-sky-500 mt-0.5" />
                    <p class="text-sky-800">
                        Jadwal seminar (tanggal, waktu, tempat, penguji) sepenuhnya ditetapkan oleh
                        <strong>Kaprodi dan Admin</strong>. Mahasiswa cukup upload berkas syarat dan klik kirim.
                    </p>
                </div>
            </div>

            @if (session('error'))
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('mahasiswa.pengajuan.seminar.store') }}"
                  enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label for="fileBerkas" class="block text-xs font-medium text-slate-700 mb-1">
                        Berkas Syarat <span class="text-slate-400">(opsional, bisa beberapa)</span>
                    </label>
                    <input id="fileBerkas" name="fileBerkas[]" type="file" multiple
                           accept=".pdf,.doc,.docx"
                           class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm
                                  file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1
                                  file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100" />
                    <p class="mt-1 text-xs text-slate-400">KRS, draft proposal, dll. PDF/DOC/DOCX · Maks 10 MB per file</p>
                    @error('fileBerkas.*') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex flex-col gap-2 pt-2">
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600 transition-colors">
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
</div>
