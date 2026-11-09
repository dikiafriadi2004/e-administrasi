<x-app-layout>
    <x-slot name="title">Ajukan Surat Keluar Prodi</x-slot>
    <div class="mb-4 flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('mahasiswa.riwayat.index') }}" class="hover:text-brand-600">Riwayat</a>
        <span>/</span><span class="text-gray-700">Keluar Prodi</span>
    </div>
    <div class="max-w-2xl mx-auto">
        <div class="rounded-xl border bg-white p-6 shadow-sm">
            <h2 class="mb-5 text-base font-semibold text-slate-800">Ajukan Surat Keluar Prodi</h2>

            @if (session('error'))
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('mahasiswa.pengajuan.keluar-prodi.store') }}" class="space-y-4">
                @csrf

                <div class="rounded-xl bg-slate-50 px-4 py-3 text-xs space-y-1.5">
                    <div class="flex gap-3"><span class="w-16 text-slate-500">Nama</span><span class="text-slate-800">{{ auth()->user()->name }}</span></div>
                    <div class="flex gap-3"><span class="w-16 text-slate-500">NIM</span><span class="font-mono text-slate-800">{{ auth()->user()->mahasiswa?->nim ?? '-' }}</span></div>
                </div>

                <div>
                    <x-input-label for="alasanKeluar" value="Alasan Keluar / Pindah Prodi *" />
                    <textarea id="alasanKeluar" name="alasanKeluar" rows="4" required
                              class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-brand-400 focus:ring-brand-400 text-sm"
                              placeholder="Contoh: Saya ingin pindah ke program studi lain karena...">{{ old('alasanKeluar') }}</textarea>
                    <p class="mt-1 text-xs text-slate-400">Maksimal 1000 karakter.</p>
                    @error('alasanKeluar') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <x-input-label for="tujuanPindah" value="Universitas / Prodi Tujuan (opsional)" />
                    <x-text-input id="tujuanPindah" name="tujuanPindah" type="text" class="mt-1 block w-full"
                                  placeholder="Contoh: Universitas Indonesia — Ilmu Komunikasi"
                                  value="{{ old('tujuanPindah') }}" />
                    @error('tujuanPindah') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
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
