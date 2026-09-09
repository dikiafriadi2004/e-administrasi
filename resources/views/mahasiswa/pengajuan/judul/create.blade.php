<x-app-layout>
    <x-slot name="title">Ajukan Judul Skripsi</x-slot>
    <div class="mb-4 flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('mahasiswa.riwayat.index') }}" class="hover:text-brand-600">Riwayat</a>
        <span>/</span>
        <span class="text-gray-700">Ajukan Judul Skripsi</span>
    </div>
    <div class="max-w-2xl mx-auto">
        <div class="rounded-xl border bg-white p-6 shadow-sm">
            <h2 class="mb-5 text-base font-semibold text-slate-800">Pengajuan Judul Skripsi</h2>

            @if (session('error'))
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('mahasiswa.pengajuan.judul.store') }}"
                  enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <x-input-label for="judul" value="Judul Skripsi *" />
                    <textarea id="judul" name="judul" rows="3" required
                              placeholder="Tulis judul skripsi yang direncanakan..."
                              class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-brand-400 focus:ring-brand-400 text-sm">{{ old('judul') }}</textarea>
                    @error('judul') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <x-input-label for="bidangKajian" value="Bidang Kajian *" />
                    <x-text-input id="bidangKajian" name="bidangKajian" type="text" class="mt-1 block w-full"
                                  placeholder="Contoh: Rekayasa Perangkat Lunak"
                                  value="{{ old('bidangKajian') }}" required />
                    @error('bidangKajian') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <x-input-label for="ringkasan" value="Ringkasan Singkat *" />
                    <textarea id="ringkasan" name="ringkasan" rows="4" required
                              placeholder="Deskripsikan secara singkat topik dan tujuan penelitian (min. 50 karakter)..."
                              class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-brand-400 focus:ring-brand-400 text-sm">{{ old('ringkasan') }}</textarea>
                    @error('ringkasan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <x-input-label for="pendekatanPenelitian" value="Pendekatan Penelitian" />
                    <x-text-input id="pendekatanPenelitian" name="pendekatanPenelitian" type="text" class="mt-1 block w-full"
                                  placeholder="Contoh: Kualitatif / Kuantitatif / Mixed Method"
                                  value="{{ old('pendekatanPenelitian') }}" />
                    @error('pendekatanPenelitian') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="namaDosenwali" value="Nama Dosen Wali" />
                        <x-text-input id="namaDosenwali" name="namaDosenwali" type="text" class="mt-1 block w-full"
                                      placeholder="Nama dosen wali Anda"
                                      value="{{ old('namaDosenwali') }}" />
                        @error('namaDosenwali') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <x-input-label for="namaDosenSpup" value="Nama Dosen SPUP" />
                        <x-text-input id="namaDosenSpup" name="namaDosenSpup" type="text" class="mt-1 block w-full"
                                      placeholder="Nama dosen SPUP"
                                      value="{{ old('namaDosenSpup') }}" />
                        @error('namaDosenSpup') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Form SPUP --}}
                <div class="rounded-xl border border-amber-100 bg-amber-50 p-4">
                    <label for="fileSpup" class="block text-xs font-semibold text-amber-800 mb-1">
                        Form SPUP yang Sudah Ditandatangani Kaprodi
                    </label>
                    <p class="mb-2 text-xs text-amber-700">Upload Form SPUP yang sudah ditandatangani Kaprodi sebagai syarat pengajuan.</p>
                    <input id="fileSpup" name="fileSpup" type="file" accept=".pdf,.doc,.docx"
                           class="block w-full rounded-xl border border-amber-200 bg-white px-3 py-2 text-sm
                                  file:mr-3 file:rounded-lg file:border-0 file:bg-amber-100 file:px-3 file:py-1
                                  file:text-sm file:font-medium file:text-amber-700 hover:file:bg-amber-200" />
                    <p class="mt-1 text-xs text-amber-600">PDF, DOC, atau DOCX · Maks 10 MB</p>
                    @error('fileSpup') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <x-input-label for="fileBerkas" value="Dokumen Pendukung Lainnya (opsional, bisa beberapa)" />
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
