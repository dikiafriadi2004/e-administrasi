<div>
    <div class="flex gap-6 items-start">

        {{-- ===== KIRI: Form Edit ===== --}}
        <div class="w-80 shrink-0">
            <div class="rounded-xl border bg-white p-6 shadow-sm">
                <h2 class="mb-1 text-base font-bold text-slate-800">Revisi Judul Skripsi</h2>
                <p class="mb-5 text-xs text-slate-400">Perbaiki data pengajuan sesuai catatan dari Kaprodi.</p>

                {{-- Banner revisi mandiri (setelah seminar, status disetujui) --}}
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

                {{-- Catatan kaprodi jika ada --}}
                @if ($pengajuan->catatan_kaprodi)
                    <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 p-3">
                        <p class="mb-1 flex items-center gap-1.5 text-xs font-semibold text-amber-800">
                            <x-icon name="message-circle" class="h-3.5 w-3.5" />
                            Catatan Kaprodi:
                        </p>
                        <p class="text-xs text-amber-700">{{ $pengajuan->catatan_kaprodi }}</p>
                    </div>
                @endif

                {{-- Berkas yang sudah diupload --}}
                @if ($pengajuan->berkas->count())
                    <div class="mb-4">
                        <p class="mb-2 text-xs font-medium text-slate-600">Berkas Terlampir:</p>
                        <ul class="space-y-1.5">
                            @foreach ($pengajuan->berkas as $berkas)
                                <li class="flex items-center justify-between rounded-lg border border-slate-100 bg-slate-50 px-3 py-1.5 text-xs">
                                    <span class="truncate text-slate-700" title="{{ $berkas->nama_asli }}">
                                        <x-icon name="file" class="mr-1 inline h-3 w-3 text-slate-400" />
                                        {{ $berkas->nama_asli }}
                                    </span>
                                    <button wire:click="hapusBerkas({{ $berkas->id }})"
                                            wire:confirm="Hapus berkas ini?"
                                            class="ml-2 shrink-0 text-red-400 hover:text-red-600">
                                        <x-icon name="x" class="h-3.5 w-3.5" />
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form wire:submit="submit" class="space-y-4">

                    <div>
                        <x-input-label for="judul" value="Judul Skripsi *" />
                        <textarea id="judul" wire:model.blur="judul" rows="3"
                                  class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-brand-400 focus:ring-brand-400 text-sm"></textarea>
                        @error('judul') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <x-input-label for="bidangKajian" value="Bidang Kajian *" />
                        <x-text-input id="bidangKajian" wire:model.blur="bidangKajian"
                                      type="text" class="mt-1 block w-full"
                                      placeholder="Contoh: Rekayasa Perangkat Lunak" />
                        @error('bidangKajian') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <x-input-label for="ringkasan" value="Ringkasan Singkat *" />
                        <textarea id="ringkasan" wire:model.blur="ringkasan" rows="4"
                                  placeholder="Deskripsikan topik dan tujuan penelitian..."
                                  class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-brand-400 focus:ring-brand-400 text-sm"></textarea>
                        @error('ringkasan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <x-input-label for="fileBerkas" value="Tambah Berkas (opsional)" />
                        <input id="fileBerkas" wire:model="fileBerkas" type="file" multiple
                               accept=".pdf,.doc,.docx"
                               class="mt-1 block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm
                                      file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1
                                      file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100" />
                        <p class="mt-1 text-xs text-slate-400">Bisa pilih beberapa file. PDF/DOC/DOCX · Maks 10 MB per file</p>
                        @error('fileBerkas') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        <div wire:loading wire:target="fileBerkas" class="mt-1 text-xs text-brand-600">Mengupload...</div>
                    </div>

                    <div class="flex flex-col gap-2 pt-2">
                        <button type="submit"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-60 cursor-not-allowed"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white transition-opacity">
                            <svg wire:loading class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span wire:loading.remove>Kirim Revisi</span><span wire:loading>Mengirim...</span>
                        </button>
                        <a href="{{ route('mahasiswa.riwayat.index') }}"
                           class="w-full text-center rounded-xl border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 transition-colors">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===== KANAN: Pratinjau perubahan ===== --}}
        <div class="flex-1 min-w-0">
            <div class="sticky top-6 rounded-2xl border border-brand-100 bg-brand-50 p-5">
                <p class="mb-4 flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-brand-600">
                    <x-icon name="eye" class="h-3.5 w-3.5" />
                    Pratinjau Data
                </p>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs font-medium text-slate-500">Judul</dt>
                        <dd class="mt-0.5 break-words text-slate-800 leading-snug">{{ $judul ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-slate-500">Bidang Kajian</dt>
                        <dd class="mt-0.5 text-slate-800">{{ $bidangKajian ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-slate-500">Ringkasan</dt>
                        <dd class="mt-0.5 text-xs leading-relaxed text-slate-600">
                            {{ $ringkasan ? \Illuminate\Support\Str::limit($ringkasan, 200) : '—' }}
                        </dd>
                    </div>
                </dl>
                <div class="mt-4 rounded-xl bg-white px-3 py-2 text-xs text-slate-500">
                    Revisi ini akan mengubah status pengajuan kembali ke <strong>Diajukan</strong> untuk ditinjau ulang oleh Kaprodi.
                </div>
            </div>
        </div>

    </div>
</div>
