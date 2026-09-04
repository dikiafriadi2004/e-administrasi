<x-app-layout>
    <x-slot name="title">Revisi Judul Skripsi</x-slot>

    <div class="mb-4 flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('mahasiswa.riwayat.index') }}" class="hover:text-brand-600 transition-colors">Riwayat</a>
        <x-icon name="chevron-right" class="h-4 w-4 text-slate-300" />
        <span class="text-slate-700 font-medium">Revisi Judul</span>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="rounded-xl border bg-white p-6 shadow-sm">
            <h2 class="mb-1 text-base font-bold text-slate-800">Revisi Judul Skripsi</h2>
            <p class="mb-5 text-xs text-slate-400">Perbaiki data pengajuan sesuai catatan dari Kaprodi.</p>

            {{-- Banner revisi mandiri --}}
            @if ($pengajuan->status === 'disetujui')
                <div class="mb-4 rounded-xl border border-orange-200 bg-orange-50 px-4 py-3">
                    <div class="flex items-start gap-2">
                        <x-icon name="info" class="h-4 w-4 shrink-0 text-orange-500 mt-0.5" />
                        <div>
                            <p class="text-xs font-semibold text-orange-800">Revisi Judul Mandiri</p>
                            <p class="mt-0.5 text-xs text-orange-700">
                                Setelah revisi diajukan, Kaprodi akan meninjau ulang dan memberikan persetujuan baru.
                                Dosen pembimbing saat ini tetap dipertahankan kecuali Kaprodi menggantinya.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Catatan kaprodi --}}
            @if ($pengajuan->catatan_kaprodi)
                <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 p-3">
                    <p class="mb-1 flex items-center gap-1.5 text-xs font-semibold text-amber-800">
                        <x-icon name="message-circle" class="h-3.5 w-3.5" />
                        Catatan Kaprodi:
                    </p>
                    <p class="text-xs text-amber-700">{{ $pengajuan->catatan_kaprodi }}</p>
                </div>
            @endif

            {{-- Berkas terlampir --}}
            @if ($pengajuan->berkas->count())
                <div class="mb-4">
                    <p class="mb-2 text-xs font-medium text-slate-600">Berkas Terlampir:</p>
                    <ul class="space-y-1.5">
                        @foreach ($pengajuan->berkas as $berkas)
                            <li class="flex items-center gap-2 rounded-lg border border-slate-100 bg-slate-50 px-3 py-1.5 text-xs">
                                <x-icon name="file" class="h-3 w-3 shrink-0 text-slate-400" />
                                <span class="flex-1 truncate text-slate-700">{{ $berkas->nama_asli }}</span>
                                <a href="{{ route('mahasiswa.berkas.download', $berkas) }}"
                                   class="text-brand-600 hover:text-brand-700">
                                    <x-icon name="download" class="h-3.5 w-3.5" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('mahasiswa.pengajuan.judul.update', $pengajuan) }}"
                  enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="judul" value="Judul Skripsi *" />
                    <textarea id="judul" name="judul" rows="3" required
                              class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-brand-400 focus:ring-brand-400 text-sm">{{ old('judul', $pengajuan->judul) }}</textarea>
                    @error('judul') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <x-input-label for="bidangKajian" value="Bidang Kajian *" />
                    <x-text-input id="bidangKajian" name="bidangKajian" type="text" class="mt-1 block w-full"
                                  value="{{ old('bidangKajian', $pengajuan->bidang_kajian) }}" required />
                    @error('bidangKajian') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <x-input-label for="ringkasan" value="Ringkasan Singkat *" />
                    <textarea id="ringkasan" name="ringkasan" rows="4" required
                              class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-brand-400 focus:ring-brand-400 text-sm">{{ old('ringkasan', $pengajuan->ringkasan) }}</textarea>
                    @error('ringkasan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <x-input-label for="fileBerkas" value="Tambah Berkas Pendukung (opsional)" />
                    <input id="fileBerkas" name="fileBerkas[]" type="file" multiple accept=".pdf,.doc,.docx"
                           class="mt-1 block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm
                                  file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1
                                  file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100" />
                    <p class="mt-1 text-xs text-slate-400">PDF, DOC, atau DOCX · Maks 10 MB per file</p>
                    @error('fileBerkas.*') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex flex-col gap-2 pt-2">
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center rounded-xl bg-brand-500 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-600 transition-colors">
                        Kirim Revisi
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
