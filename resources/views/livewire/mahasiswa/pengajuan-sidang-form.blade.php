<div>
    <div class="flex gap-6 items-start">

        {{-- ===== KIRI: Form ===== --}}
        <div class="w-72 shrink-0">
            <div class="rounded-xl border bg-white p-6 shadow-sm">
                <h2 class="mb-5 text-base font-semibold text-slate-800">Pengajuan Sidang Skripsi</h2>

                {{-- Data auto-terisi --}}
                <div class="mb-4 rounded-xl border border-brand-100 bg-brand-50 p-4 text-xs">
                    <p class="mb-2 font-semibold text-brand-700">Data dari Judul yang Disetujui</p>
                    <div class="space-y-1.5">
                        <div class="flex gap-2">
                            <span class="w-24 shrink-0 text-slate-500">Judul:</span>
                            <span class="font-medium leading-snug text-slate-800">{{ $pengajuanJudul->judul }}</span>
                        </div>
                        <div class="flex gap-2">
                            <span class="w-24 shrink-0 text-slate-500">Pembimbing:</span>
                            <span class="text-slate-800">{{ $pengajuanJudul->dosenPembimbing?->nama ?? '—' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Info jadwal --}}
                <div class="mb-4 rounded-xl border border-amber-100 bg-amber-50 px-4 py-3 text-xs">
                    <div class="flex items-start gap-2">
                        <x-icon name="info" class="h-3.5 w-3.5 shrink-0 text-amber-500 mt-0.5" />
                        <p class="text-amber-800">
                            <strong>Jadwal resmi</strong> (tanggal, waktu, tempat, penguji) ditetapkan oleh Kaprodi.
                            Anda boleh mengusulkan tanggal, namun Kaprodi berhak menggantinya.
                        </p>
                    </div>
                </div>

                <form wire:submit="submit" class="space-y-4">

                    {{-- Usulan Tanggal (opsional) --}}
                    <div>
                        <x-input-label for="tanggalRencana" value="Usulan Tanggal Sidang" />
                        <p class="mb-1 text-[10px] text-slate-400">Opsional — Kaprodi dapat menyetujui atau mengganti.</p>
                        <x-text-input id="tanggalRencana"
                                      wire:model.blur="tanggalRencana"
                                      type="date" class="mt-1 block w-full"
                                      min="{{ now()->addDay()->format('Y-m-d') }}" />
                        @error('tanggalRencana') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Berkas Syarat --}}
                    <div>
                        <x-input-label for="fileBerkas" value="Berkas Syarat (opsional, bisa beberapa)" />
                        <input id="fileBerkas" wire:model="fileBerkas" type="file" multiple
                               accept=".pdf,.doc,.docx"
                               class="mt-1 block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm
                                      file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1
                                      file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100" />
                        <p class="mt-1 text-xs text-slate-400">KRS, softcopy skripsi final, bebas pustaka, dll. PDF/DOC/DOCX · Maks 10 MB per file</p>
                        @error('fileBerkas') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        <div wire:loading wire:target="fileBerkas" class="mt-1 text-xs text-brand-600">Mengupload...</div>
                    </div>

                    <div class="flex flex-col gap-2 pt-2">
                        <button type="submit"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-60 cursor-not-allowed"
                                wire:target="submit"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-brand-500 px-5 py-2 text-sm font-semibold text-white transition-opacity hover:bg-brand-600">
                            <svg wire:loading wire:target="submit" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="submit">Kirim Pengajuan</span>
                            <span wire:loading wire:target="submit">Mengirim...</span>
                        </button>
                        <a href="{{ route('mahasiswa.riwayat.index') }}"
                           class="w-full text-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===== KANAN: Preview dari Template Word ===== --}}
        <div class="flex-1 min-w-0">
            @php
                $previewParams = http_build_query(array_filter([
                    'jenis'             => 'sidang_skripsi',
                    'nama_mahasiswa'    => auth()->user()?->name ?? '',
                    'nim'               => auth()->user()?->mahasiswa?->nim ?? '',
                    'judul_skripsi'     => $pengajuanJudul->judul,
                    'bidang_kajian'     => $pengajuanJudul->bidang_kajian ?? '',
                    'nama_pembimbing'   => $pengajuanJudul->dosenPembimbing?->nama ?? '',
                    'nip_pembimbing'    => $pengajuanJudul->dosenPembimbing?->nip ?? '',
                    'nama_pembimbing_1' => $pengajuanJudul->dosenPembimbing?->nama ?? '',
                    'nip_pembimbing_1'  => $pengajuanJudul->dosenPembimbing?->nip ?? '',
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
                        title="Pratinjau Surat Sidang Skripsi">
                </iframe>
            </div>
            <p class="mt-2 text-center text-xs text-slate-400">Penguji & jadwal resmi ditentukan oleh Kaprodi</p>
        </div>

    </div>
</div>
