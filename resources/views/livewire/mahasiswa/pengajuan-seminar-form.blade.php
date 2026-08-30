<div>
    <div class="max-w-2xl mx-auto">

        {{-- ===== KIRI: Form Input ===== --}}
        <div>
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
                <div class="mb-4 rounded-xl border border-amber-100 bg-amber-50 px-4 py-3 text-xs">
                    <div class="flex items-start gap-2">
                        <x-icon name="info" class="h-3.5 w-3.5 shrink-0 text-amber-500 mt-0.5" />
                        <p class="text-amber-800">
                            <strong>Jadwal resmi</strong> (tanggal, waktu, tempat, penguji) ditetapkan oleh Kaprodi.
                            Anda boleh mengusulkan tanggal yang diinginkan, namun Kaprodi berhak menggantinya.
                        </p>
                    </div>
                </div>

                <form wire:submit="submit" class="space-y-4">

                    {{-- Usulan Tanggal (opsional) --}}
                    <div>
                        <label for="tanggalRencana" class="block text-xs font-medium text-slate-700 mb-1">
                            Usulan Tanggal Seminar
                            <span class="font-normal text-slate-400">(opsional)</span>
                        </label>
                        <input id="tanggalRencana" type="date"
                               wire:model.blur="tanggalRencana"
                               min="{{ now()->addDay()->format('Y-m-d') }}"
                               class="block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400" />
                        <p class="mt-1 text-[10px] text-slate-400">Kaprodi dapat menyetujui atau mengganti tanggal ini.</p>
                        @error('tanggalRencana') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Berkas Syarat --}}
                    <div>
                        <label for="fileBerkas" class="block text-xs font-medium text-slate-700 mb-1">
                            Berkas Syarat <span class="text-slate-400">(opsional, bisa beberapa)</span>
                        </label>
                        <input id="fileBerkas" wire:model="fileBerkas" type="file" multiple
                               accept=".pdf,.doc,.docx"
                               class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm
                                      file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1
                                      file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100" />
                        <p class="mt-1 text-xs text-slate-400">KRS, draft proposal, dll. PDF/DOC/DOCX · Maks 10 MB per file</p>
                        @error('fileBerkas') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        <div wire:loading wire:target="fileBerkas" class="mt-1 text-xs text-brand-600">Mengupload...</div>
                    </div>

                    <div class="flex flex-col gap-2 pt-2">
                        <button type="submit"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-60 cursor-not-allowed"
                                wire:target="submit"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white transition-opacity hover:bg-brand-600">
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
    </div>
</div>
