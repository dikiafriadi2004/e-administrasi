<div>
    <div class="flex gap-6 items-start">

        {{-- ===== KIRI: Form Input ===== --}}
        <div class="w-72 shrink-0">
            <div class="rounded-xl border bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold text-slate-800">Surat Rekomendasi Magang</h2>

                <form wire:submit="submit" class="space-y-4">

                    {{-- Nama Lengkap (read-only) --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Nama Lengkap</label>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                            {{ auth()->user()?->name ?? '—' }}
                        </div>
                    </div>

                    {{-- NIM (read-only) --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">NIM</label>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-mono text-slate-700">
                            {{ auth()->user()?->mahasiswa?->nim ?? '—' }}
                        </div>
                    </div>

                    {{-- Nama Instansi --}}
                    <div>
                        <label for="namaInstansi" class="block text-xs font-medium text-slate-700 mb-1">
                            Nama Instansi Tujuan <span class="text-red-500">*</span>
                        </label>
                        <input id="namaInstansi" type="text"
                               wire:model.blur="namaInstansi"
                               placeholder="Contoh: PT. Teknologi Nusantara"
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

                    {{-- Upload Surat Instansi (Opsional) --}}
                    <div>
                        <label for="fileSuratInstansi" class="block text-xs font-medium text-slate-700 mb-1">
                            Surat Pengajuan dari Instansi
                            <span class="font-normal text-slate-400">(opsional)</span>
                        </label>
                        <input id="fileSuratInstansi" type="file"
                               wire:model="fileSuratInstansi"
                               accept=".pdf,.doc,.docx"
                               class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-2.5 file:py-1 file:text-xs file:font-medium file:text-brand-700 focus:outline-none" />
                        <p class="mt-1 text-xs text-slate-400">PDF, DOC, atau DOCX — maks. 5 MB</p>
                        @error('fileSuratInstansi') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Tombol --}}
                    <div class="flex flex-col gap-2 pt-2">
                        <button type="submit"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-60 cursor-not-allowed"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white transition-opacity">
                            <svg wire:loading class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span wire:loading.remove>Ajukan ke Admin</span><span wire:loading>Mengirim...</span>
                        </button>
                        <a href="{{ route('mahasiswa.riwayat.index') }}"
                           class="w-full text-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===== KANAN: Preview Surat dari Template Word ===== --}}
        
        {{-- ===== KANAN: Preview Surat (Static) ===== --}}
        <div class="flex-1 min-w-0">
            @php
                $previewParams = http_build_query(array_filter([
                    'jenis' => 'rekomendasi_magang',
                    'nama_mahasiswa' => auth()->user()?->name ?? '',
                    'nim'            => auth()->user()?->mahasiswa?->nim ?? '',
                ], fn($v) => $v !== null && $v !== ''));
            @endphp

            <div class="mb-2 flex items-center justify-between">
                <p class="flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-slate-500">
                    <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    Pratinjau Surat
                </p>
                <p class="text-xs text-slate-400">Preview dari template aktif</p>
            </div>

            <div class="rounded-xl border bg-white shadow-sm overflow-hidden" style="min-height: 297mm;">
                <iframe src="{{ route('preview-surat') }}?{{ $previewParams }}"
                        style="width: 100%; min-height: 297mm; border: none;"
                        title="Pratinjau Surat">
                </iframe>
            </div>
            <p class="mt-2 text-center text-xs text-slate-400">Preview dari template Word aktif</p>
        </div>

    </div>
</div>
