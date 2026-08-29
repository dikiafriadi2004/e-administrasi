<div>
    <div class="flex gap-6 items-start">

        {{-- ===== KIRI: Form ===== --}}
        <div class="w-72 shrink-0">
            <div class="rounded-xl border bg-white p-6 shadow-sm">
                <h2 class="mb-5 text-base font-semibold text-slate-800">Pengajuan Judul Skripsi</h2>

                <form wire:submit="submit" class="space-y-4">

                    {{-- Error global (aktif pengajuan, dll) --}}
                    @if ($errors->has('judul') && str_contains($errors->first('judul'), 'aktif') || str_contains($errors->first('judul') ?? '', 'mahasiswa'))
                        <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                            <p class="flex items-center gap-1.5">
                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 3h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                {{ $errors->first('judul') }}
                            </p>
                        </div>
                    @endif

                    <div>
                        <x-input-label for="judul" value="Judul Skripsi *" />
                        <textarea id="judul"
                                  wire:model.blur="judul"
                                  rows="3"
                                  placeholder="Tulis judul skripsi yang direncanakan..."
                                  class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-brand-400 focus:ring-brand-400 text-sm"></textarea>
                        @error('judul') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <x-input-label for="bidangKajian" value="Bidang Kajian *" />
                        <x-text-input id="bidangKajian"
                                      wire:model.blur="bidangKajian"
                                      type="text" class="mt-1 block w-full"
                                      placeholder="Contoh: Rekayasa Perangkat Lunak" />
                        @error('bidangKajian') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <x-input-label for="ringkasan" value="Ringkasan Singkat *" />
                        <textarea id="ringkasan"
                                  wire:model.blur="ringkasan"
                                  rows="4"
                                  placeholder="Deskripsikan secara singkat topik dan tujuan penelitian (min. 50 karakter)..."
                                  class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-brand-400 focus:ring-brand-400 text-sm"></textarea>
                        @error('ringkasan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <x-input-label for="fileBerkas" value="Dokumen Pendukung (opsional, bisa beberapa)" />
                        <input id="fileBerkas" wire:model="fileBerkas" type="file" multiple
                               accept=".pdf,.doc,.docx"
                               class="mt-1 block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm
                                      file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1
                                      file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100" />
                        <p class="mt-1 text-xs text-slate-400">PDF, DOC, atau DOCX · Maks 10 MB per file</p>
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

        {{-- ===== KANAN: Pratinjau Data ===== --}}
        <div class="flex-1 min-w-0">
            <div class="sticky top-6 rounded-2xl border border-brand-100 bg-brand-50 p-5">
                <p class="mb-4 flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-brand-600">
                    <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    Pratinjau Data
                </p>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs font-medium text-slate-500">Nama Mahasiswa</dt>
                        <dd class="mt-0.5 text-slate-800">{{ auth()->user()?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-slate-500">NIM</dt>
                        <dd class="mt-0.5 font-mono text-slate-800">{{ auth()->user()?->mahasiswa?->nim ?? '—' }}</dd>
                    </div>
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
                            {{ $ringkasan ? \Illuminate\Support\Str::limit($ringkasan, 150) : '—' }}
                        </dd>
                    </div>
                </dl>
                <div class="mt-4 rounded-xl bg-white px-3 py-2 text-xs text-slate-500">
                    Pratinjau ini bukan tampilan surat resmi — hanya ringkasan data yang akan diajukan.
                </div>
            </div>
        </div>

    </div>
</div>
