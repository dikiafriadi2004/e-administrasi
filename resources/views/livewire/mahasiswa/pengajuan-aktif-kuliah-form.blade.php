<div>
    <div class="flex gap-6 items-start">

        {{-- ===== KIRI: Form Input ===== --}}
        <div class="w-72 shrink-0">
            <div class="rounded-xl border bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold text-slate-800">Surat Aktif Kuliah</h2>

                <form wire:submit="submit"
                      x-data="{ keperluan: @entangle('keperluan').live }"
                      class="space-y-4">

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

                    {{-- Program Studi (read-only) --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Program Studi</label>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                            {{ App\Models\Pengaturan::nilai('nama_prodi', 'Program Studi') }}
                        </div>
                    </div>

                    {{-- Keperluan Surat (dropdown) --}}
                    <div>
                        <label for="keperluan" class="block text-xs font-medium text-slate-700 mb-1">
                            Keperluan Surat <span class="text-red-500">*</span>
                        </label>
                        <select id="keperluan"
                                wire:model.blur="keperluan"
                                x-model="keperluan"
                                class="block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400">
                            <option value="">-- Pilih Keperluan --</option>
                            @foreach ($pilihanKeperluan as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('keperluan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Keperluan Manual (muncul jika pilih "lainnya") --}}
                    <div x-show="keperluan === 'lainnya'" x-cloak>
                        <label for="keperluanManual" class="block text-xs font-medium text-slate-700 mb-1">
                            Sebutkan Keperluan <span class="text-red-500">*</span>
                        </label>
                        <input id="keperluanManual" type="text"
                               wire:model.blur="keperluanManual"
                               placeholder="Contoh: pengurusan visa pertukaran pelajar"
                               class="block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400" />
                        @error('keperluanManual') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Tujuan Instansi --}}
                    <div>
                        <label for="tujuanInstansi" class="block text-xs font-medium text-slate-700 mb-1">
                            Ditujukan Kepada <span class="text-slate-400 text-xs font-normal">(opsional)</span>
                        </label>
                        <input id="tujuanInstansi" type="text"
                               wire:model.blur="tujuanInstansi"
                               placeholder="Contoh: Kepala Dinas Pendidikan Kota X"
                               class="block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400" />
                        @error('tujuanInstansi') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
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
                    'jenis' => 'aktif_kuliah',
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
