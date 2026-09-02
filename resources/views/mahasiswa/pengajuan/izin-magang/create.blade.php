<x-app-layout>
    <x-slot name="title">Ajukan Izin Magang / PKL</x-slot>
    <div class="mb-4 flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('mahasiswa.riwayat.index') }}" class="hover:text-brand-600">Riwayat</a>
        <span>/</span><span class="text-gray-700">Izin Magang</span>
    </div>
    <div class="max-w-2xl mx-auto">
        <div class="rounded-xl border bg-white p-6 shadow-sm">
            <h2 class="mb-5 text-base font-semibold text-slate-800">Ajukan Surat Izin Magang / PKL</h2>
            <form method="POST" action="{{ route('mahasiswa.pengajuan.izin-magang.store') }}"
                  enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <x-input-label for="namaInstansi" value="Nama Instansi / Perusahaan *" />
                    <x-text-input id="namaInstansi" name="namaInstansi" type="text" class="mt-1 block w-full"
                                  placeholder="Contoh: PT. Teknologi Nusantara" value="{{ old('namaInstansi') }}" required />
                    @error('namaInstansi') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <x-input-label for="alamatInstansi" value="Alamat Lengkap Instansi *" />
                    <textarea id="alamatInstansi" name="alamatInstansi" rows="2" required
                              class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-brand-400 focus:ring-brand-400 text-sm"
                              placeholder="Jl. Contoh No. 1, Kota">{{ old('alamatInstansi') }}</textarea>
                    @error('alamatInstansi') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <x-input-label for="tanggalMulai" value="Tanggal Mulai *" />
                        <x-text-input id="tanggalMulai" name="tanggalMulai" type="date" class="mt-1 block w-full"
                                      value="{{ old('tanggalMulai') }}" required />
                        @error('tanggalMulai') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <x-input-label for="tanggalSelesai" value="Tanggal Selesai *" />
                        <x-text-input id="tanggalSelesai" name="tanggalSelesai" type="date" class="mt-1 block w-full"
                                      value="{{ old('tanggalSelesai') }}" required />
                        @error('tanggalSelesai') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <x-input-label for="fileSuratInstansi" value="Surat dari Instansi (opsional)" />
                    <input id="fileSuratInstansi" name="fileSuratInstansi" type="file" accept=".pdf,.doc,.docx"
                           class="mt-1 block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm
                                  file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1
                                  file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100" />
                    <p class="mt-1 text-xs text-slate-400">PDF, DOC, atau DOCX · Maks 5 MB</p>
                    @error('fileSuratInstansi') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex flex-col gap-2 pt-2">
                    <button type="submit" class="w-full inline-flex items-center justify-center rounded-xl bg-brand-500 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-600 transition-colors">
                        Kirim Pengajuan
                    </button>
                    <a href="{{ route('mahasiswa.riwayat.index') }}" class="w-full text-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
