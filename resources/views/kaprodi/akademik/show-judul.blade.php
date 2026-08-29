<x-app-layout>
    <x-slot name="title">Keputusan Pengajuan Judul</x-slot>

    <div class="mb-4 flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('kaprodi.akademik.index') }}" class="hover:text-brand-600">Antrian Akademik</a>
        <x-icon name="chevron-right" class="h-4 w-4 text-slate-300" />
        <span class="text-slate-700">Detail Judul</span>
    </div>

    <div class="mx-auto max-w-3xl space-y-5"
         x-data="{ modalSetujui: false, modalTolak: false }">

        {{-- Info Judul --}}
        <div class="rounded-xl border bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-slate-800">Pengajuan Judul Skripsi</h2>
                    <p class="mt-0.5 text-xs text-slate-400">Diajukan {{ $pengajuan->created_at->format('d M Y, H:i') }}</p>
                </div>
                <x-status-badge :status="$pengajuan->status" />
            </div>
            <dl class="space-y-3 text-sm">
                <div class="grid grid-cols-3 gap-2">
                    <dt class="font-medium text-slate-500">Mahasiswa</dt>
                    <dd class="col-span-2 text-slate-800">{{ $pengajuan->mahasiswa->user->name }}
                        <span class="text-slate-400">({{ $pengajuan->mahasiswa->nim }})</span></dd>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <dt class="font-medium text-slate-500">Judul</dt>
                    <dd class="col-span-2 leading-relaxed text-slate-800">{{ $pengajuan->judul }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <dt class="font-medium text-slate-500">Bidang Kajian</dt>
                    <dd class="col-span-2 text-slate-800">{{ $pengajuan->bidang_kajian }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <dt class="font-medium text-slate-500">Ringkasan</dt>
                    <dd class="col-span-2 leading-relaxed text-gray-700 text-sm">{{ $pengajuan->ringkasan }}</dd>
                </div>
                @if ($pengajuan->berkas->count())
                    <div class="grid grid-cols-3 gap-2">
                        <dt class="font-medium text-slate-500">Berkas Syarat</dt>
                        <dd class="col-span-2 space-y-1">
                            @foreach ($pengajuan->berkas as $berkas)
                                <a href="{{ route('kaprodi.berkas.download', $berkas) }}"
                                   class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs text-slate-600 hover:bg-slate-100 transition-colors">
                                    <x-icon name="file" class="h-3 w-3 text-slate-400" />
                                    {{ $berkas->nama_asli }}
                                </a>
                            @endforeach
                        </dd>
                    </div>
                @endif
            </dl>
        </div>

        @if ($pengajuan->status === 'diajukan')
            {{-- Tabel Pilih Pembimbing (1 saja) --}}
            <div class="rounded-xl border bg-white p-6 shadow-sm"
                 x-data="{ pembimbing: '', pembimbingNama: '' }">
                <h3 class="mb-1 text-sm font-semibold text-slate-700">Pilih Dosen Pembimbing</h3>
                <p class="mb-4 text-xs text-slate-400">Wajib dipilih sebelum menyetujui. Diurutkan dari beban bimbingan terkecil.</p>
                <div class="overflow-hidden rounded-lg border">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-4 py-2 text-left">#</th>
                                <th class="px-4 py-2 text-left">Nama Dosen</th>
                                <th class="px-4 py-2 text-center">Bimbingan Aktif</th>
                                <th class="px-4 py-2 text-center">Kapasitas</th>
                                <th class="px-4 py-2 text-center">Status</th>
                                <th class="px-4 py-2 text-center">Pilih</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($dosenTerurut as $i => $dosen)
                                <tr class="{{ $i === 0 ? 'bg-green-50' : 'hover:bg-gray-50' }}">
                                    <td class="px-4 py-2 text-xs text-slate-400">{{ $i + 1 }}</td>
                                    <td class="px-4 py-2">
                                        <p class="font-medium text-slate-800">{{ $dosen->nama }}</p>
                                        <p class="font-mono text-xs text-slate-400">{{ $dosen->nip }}</p>
                                    </td>
                                    <td class="px-4 py-2 text-center font-bold text-slate-700">{{ $dosen->jumlah_bimbingan }}</td>
                                    <td class="px-4 py-2 text-center text-slate-500">{{ $dosen->kapasitas_maksimal ?? '∞' }}</td>
                                    <td class="px-4 py-2 text-center">
                                        @if ($dosen->isKapasitasPenuh())
                                            <span class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">Penuh</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">Tersedia</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <button type="button"
                                                @click="pembimbing = '{{ $dosen->id }}'; pembimbingNama = '{{ addslashes($dosen->nama) }}'"
                                                :class="pembimbing === '{{ $dosen->id }}' ? 'bg-brand-500 text-white' : 'border border-brand-300 text-brand-600 hover:bg-brand-50'"
                                                class="rounded-lg px-3 py-1 text-xs font-medium transition-colors">
                                            <span x-text="pembimbing === '{{ $dosen->id }}' ? '✓ Dipilih' : 'Pilih'"></span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Ringkasan --}}
                <div class="mt-3 rounded-lg bg-gray-50 px-4 py-3 text-xs">
                    <div class="flex gap-2">
                        <span class="w-28 font-medium text-slate-500">Pembimbing:</span>
                        <span x-text="pembimbingNama || '— belum dipilih —'"
                              :class="pembimbingNama ? 'font-semibold text-brand-700' : 'text-slate-400 italic'"></span>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-3">
                    <button @click="if (!pembimbing) { $dispatch('notify', {type:'warning', message:'Pilih dosen pembimbing terlebih dahulu.'}); return; } modalSetujui = true"
                            class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors">
                        <x-icon name="check-circle" class="h-4 w-4" />
                        Setujui & Tetapkan Pembimbing
                    </button>
                    <button @click="modalTolak = true"
                            class="inline-flex items-center gap-2 rounded-xl border border-red-200 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition-colors">
                        <x-icon name="x-circle" class="h-4 w-4" />
                        Tolak Pengajuan
                    </button>
                </div>

                {{-- Modal Setujui --}}
                <div x-show="modalSetujui" x-cloak
                     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
                     x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <div @click.stop class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl"
                         x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100">
                            <x-icon name="check-circle" class="h-6 w-6 text-emerald-600" />
                        </div>
                        <h3 class="mb-2 text-base font-bold text-slate-900">Konfirmasi Persetujuan</h3>
                        <p class="mb-3 text-sm text-slate-600">
                            Judul dari <strong>{{ $pengajuan->mahasiswa->user->name }}</strong> akan disetujui dengan dosen pembimbing:
                        </p>
                        <div class="mb-4 rounded-xl bg-brand-50 px-3 py-2 text-sm">
                            <div class="flex gap-2">
                                <span class="w-28 text-slate-500">Pembimbing:</span>
                                <span class="font-semibold text-brand-700" x-text="pembimbingNama"></span>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3">
                            <button @click="modalSetujui = false"
                                    class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                                Batal
                            </button>
                            <form method="POST" action="{{ route('kaprodi.akademik.judul.setujui', $pengajuan) }}">
                                @csrf
                                <input type="hidden" name="dosen_pembimbing_id" :value="pembimbing" />
                                <button type="submit"
                                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors">
                                    <x-icon name="check" class="h-4 w-4" />
                                    Ya, Setujui
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Tolak --}}
            <div x-show="modalTolak" x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
                 x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <div @click.stop class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl"
                     x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-red-100">
                        <x-icon name="x-circle" class="h-6 w-6 text-red-600" />
                    </div>
                    <h3 class="mb-2 text-base font-bold text-slate-900">Tolak Pengajuan Judul</h3>
                    <p class="mb-4 text-sm text-slate-600">Berikan alasan yang jelas agar mahasiswa dapat memperbaiki judul.</p>
                    <form method="POST" action="{{ route('kaprodi.akademik.judul.tolak', $pengajuan) }}" class="space-y-4">
                        @csrf
                        <textarea name="catatan_penolakan" rows="3" required
                                  placeholder="Contoh: Judul terlalu umum, perlu metode yang lebih spesifik..."
                                  class="block w-full rounded-xl border-slate-200 text-sm focus:border-red-400 focus:ring-red-400">{{ old('catatan_penolakan') }}</textarea>
                        @error('catatan_penolakan') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        <div class="flex justify-end gap-3">
                            <button type="button" @click="modalTolak = false"
                                    class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 transition-colors">
                                <x-icon name="x-circle" class="h-4 w-4" />
                                Tolak
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        @elseif ($pengajuan->status === 'disetujui')
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                <div class="flex items-center gap-2 mb-2">
                    <x-icon name="circle-check" class="h-5 w-5 text-emerald-600" />
                    <p class="text-sm font-semibold text-emerald-800">Judul Disetujui</p>
                </div>
                <p class="text-sm text-emerald-700">
                    Pembimbing: <strong>{{ $pengajuan->dosenPembimbing?->nama ?? '—' }}</strong>
                </p>
            </div>
        @elseif ($pengajuan->status === 'ditolak')
            <div class="rounded-2xl border border-red-200 bg-red-50 p-4">
                <div class="flex items-center gap-2 mb-1">
                    <x-icon name="circle-x" class="h-5 w-5 text-red-500" />
                    <p class="text-sm font-semibold text-red-800">Ditolak</p>
                </div>
                <p class="mt-1 text-sm text-red-700">{{ $pengajuan->catatan_penolakan }}</p>
            </div>
        @endif

        {{-- Riwayat Status --}}
        <div class="rounded-xl border bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold text-slate-700">Riwayat Status</h3>
            <ol class="relative ml-2 space-y-4 border-l border-gray-200">
                @forelse ($pengajuan->statusHistories as $h)
                    <li class="ml-4">
                        <div class="absolute -left-1.5 mt-1.5 h-3 w-3 rounded-full border-2 border-white bg-brand-400 shadow-sm"></div>
                        <p class="text-xs text-slate-400">{{ $h->created_at?->format('d M Y, H:i') }} — {{ $h->changedBy?->name }}</p>
                        <p class="text-sm font-medium text-slate-700">→ <span class="font-semibold">{{ $h->status_baru }}</span></p>
                        @if ($h->catatan) <p class="mt-0.5 text-xs text-slate-500">{{ $h->catatan }}</p> @endif
                    </li>
                @empty
                    <li class="ml-4 text-sm text-slate-400">Belum ada riwayat.</li>
                @endforelse
            </ol>
        </div>
    </div>
</x-app-layout>
