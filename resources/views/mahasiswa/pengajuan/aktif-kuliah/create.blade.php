<x-app-layout>
    <x-slot name="title">Ajukan Surat Aktif Kuliah</x-slot>
    <div class="mb-4 flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('mahasiswa.riwayat.index') }}" class="hover:text-brand-600">Riwayat</a>
        <span>/</span>
        <span class="text-gray-700">Surat Aktif Kuliah</span>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="rounded-xl border bg-white p-6 shadow-sm">
            <h2 class="mb-5 text-base font-semibold text-slate-800">Ajukan Surat Aktif Kuliah</h2>

            @if (session('error'))
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('mahasiswa.pengajuan.aktif-kuliah.store') }}" class="space-y-4">
                @csrf

                <div>
                    <x-input-label for="keperluan" value="Keperluan Surat *" />
                    <select id="keperluan" name="keperluan" required
                            class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-brand-400 focus:ring-brand-400 text-sm"
                            onchange="document.getElementById('wrap-manual').style.display = this.value === 'lainnya' ? 'block' : 'none'">
                        <option value="">-- Pilih Keperluan --</option>
                        <option value="Melamar Beasiswa" {{ old('keperluan') === 'Melamar Beasiswa' ? 'selected' : '' }}>Melamar Beasiswa</option>
                        <option value="Magang / Praktik Kerja Lapangan (PKL)" {{ old('keperluan') === 'Magang / Praktik Kerja Lapangan (PKL)' ? 'selected' : '' }}>Magang / PKL</option>
                        <option value="Administrasi Perbankan / Asuransi" {{ old('keperluan') === 'Administrasi Perbankan / Asuransi' ? 'selected' : '' }}>Administrasi Perbankan / Asuransi</option>
                        <option value="Keperluan Akademik" {{ old('keperluan') === 'Keperluan Akademik' ? 'selected' : '' }}>Keperluan Akademik</option>
                        <option value="Pengurusan Visa / Pertukaran Pelajar" {{ old('keperluan') === 'Pengurusan Visa / Pertukaran Pelajar' ? 'selected' : '' }}>Pengurusan Visa / Pertukaran Pelajar</option>
                        <option value="lainnya" {{ old('keperluan') === 'lainnya' ? 'selected' : '' }}>Lainnya (isi manual)</option>
                    </select>
                    @error('keperluan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div id="wrap-manual" style="{{ old('keperluan') === 'lainnya' ? '' : 'display:none' }}">
                    <x-input-label for="keperluanManual" value="Isi Keperluan Manual *" />
                    <x-text-input id="keperluanManual" name="keperluanManual" type="text"
                                  class="mt-1 block w-full" placeholder="Contoh: Keperluan administrasi BPJS"
                                  value="{{ old('keperluanManual') }}" />
                    @error('keperluanManual') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <x-input-label for="tujuanInstansi" value="Ditujukan Kepada (opsional)" />
                    <x-text-input id="tujuanInstansi" name="tujuanInstansi" type="text"
                                  class="mt-1 block w-full" placeholder="Contoh: Kepala Dinas Pendidikan Kota"
                                  value="{{ old('tujuanInstansi') }}" />
                </div>

                <div class="flex flex-col gap-2 pt-2">
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-brand-500 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-600 transition-colors">
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
