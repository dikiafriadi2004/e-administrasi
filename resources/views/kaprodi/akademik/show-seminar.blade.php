<x-app-layout>
    <x-slot name="title">Keputusan Seminar Proposal</x-slot>

    <div class="mb-4 flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('kaprodi.akademik.index') }}" class="hover:text-brand-600">Antrian Akademik</a>
        <x-icon name="chevron-right" class="h-4 w-4 text-slate-300" />
        <span class="text-slate-700">Seminar Proposal</span>
    </div>

    <div class="mx-auto max-w-3xl space-y-5"
         x-data="{ modalSetujui: false, modalTolak: false, penguji1: '', penguji1Nama: '', penguji2: '', penguji2Nama: '' }">

        {{-- Info --}}
        <div class="rounded-xl border bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-slate-800">Pengajuan Seminar Proposal</h2>
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
                    <dt class="font-medium text-slate-500">Judul Skripsi</dt>
                    <dd class="col-span-2 leading-relaxed text-slate-800">{{ $pengajuan->pengajuanJudul?->judul ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <dt class="font-medium text-slate-500">Bidang Kajian</dt>
                    <dd class="col-span-2 text-slate-800">{{ $pengajuan->pengajuanJudul?->bidang_kajian ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <dt class="font-medium text-slate-500">Dosen Pembimbing</dt>
                    <dd class="col-span-2 text-slate-800">{{ $pengajuan->pengajuanJudul?->dosenPembimbing?->nama ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <dt class="font-medium text-slate-500">Rencana Tanggal</dt>
                    <dd class="col-span-2 text-slate-800">{{ $pengajuan->data_form['tanggal_rencana'] ?? '—' }}</dd>
                </div>
                @if ($pengajuan->dosenPenguji)
                    <div class="grid grid-cols-3 gap-2">
                        <dt class="font-medium text-slate-500">Penguji 1 Ditetapkan</dt>
                        <dd class="col-span-2 font-semibold text-brand-700">{{ $pengajuan->dosenPenguji->nama }}</dd>
                    </div>
                @endif
                @if ($pengajuan->dosenPenguji2)
                    <div class="grid grid-cols-3 gap-2">
                        <dt class="font-medium text-slate-500">Penguji 2 Ditetapkan</dt>
                        <dd class="col-span-2 font-semibold text-teal-700">{{ $pengajuan->dosenPenguji2->nama }}</dd>
                    </div>
                @endif
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
            {{-- Tabel Pilih Penguji --}}
            <div class="rounded-xl border bg-white p-6 shadow-sm">
                <h3 class="mb-1 text-sm font-semibold text-slate-700">Pilih Dosen Penguji</h3>
                <p class="mb-1 text-xs text-slate-400">Dosen pembimbing sudah di-exclude. <strong>Penguji 1 dan Penguji 2 wajib dipilih.</strong> Diurutkan dari beban pengujian terkecil.</p>
                <p class="mb-4 text-xs text-brand-600 font-medium">
                    <x-icon name="calendar" class="h-3 w-3 inline mr-1" />
                    Rasio dihitung untuk tahun akademik: {{ app(\App\Services\RasioDosenService::class)->getTahunAktif() }}
                </p>
                <div class="overflow-hidden rounded-lg border">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-4 py-2 text-left">#</th>
                                <th class="px-4 py-2 text-left">Nama Dosen</th>
                                <th class="px-4 py-2 text-center">Penugasan Aktif</th>
                                <th class="px-4 py-2 text-center">Penguji 1</th>
                                <th class="px-4 py-2 text-center">Penguji 2</th>
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
                                    <td class="px-4 py-2 text-center font-bold text-slate-700">{{ $dosen->jumlah_pengujian }}</td>
                                    <td class="px-4 py-2 text-center">
                                        <button type="button"
                                                @click="penguji1 = '{{ $dosen->id }}'; penguji1Nama = '{{ addslashes($dosen->nama) }}'; if (penguji2 === '{{ $dosen->id }}') { penguji2 = ''; penguji2Nama = ''; }"
                                                :class="penguji1 === '{{ $dosen->id }}' ? 'bg-brand-500 text-white' : 'border border-brand-300 text-brand-600 hover:bg-brand-50'"
                                                class="rounded-lg px-3 py-1 text-xs font-medium transition-colors">
                                            <span x-text="penguji1 === '{{ $dosen->id }}' ? '✓ P1' : 'P1'"></span>
                                        </button>
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <button type="button"
                                                @click="if (penguji1 === '{{ $dosen->id }}') return; penguji2 = (penguji2 === '{{ $dosen->id }}' ? '' : '{{ $dosen->id }}'); penguji2Nama = (penguji2 === '{{ $dosen->id }}' ? '{{ addslashes($dosen->nama) }}' : '')"
                                                :disabled="penguji1 === '{{ $dosen->id }}'"
                                                :class="penguji2 === '{{ $dosen->id }}' ? 'bg-teal-600 text-white' : (penguji1 === '{{ $dosen->id }}' ? 'opacity-30 cursor-not-allowed border border-gray-200 text-gray-400' : 'border border-teal-300 text-teal-600 hover:bg-teal-50')"
                                                class="rounded-lg px-3 py-1 text-xs font-medium transition-colors">
                                            <span x-text="penguji2 === '{{ $dosen->id }}' ? '✓ P2' : 'P2'"></span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Ringkasan --}}
                <div class="mt-3 rounded-lg bg-gray-50 px-4 py-3 text-xs space-y-1">
                    <div class="flex gap-2">
                        <span class="w-20 font-medium text-slate-500">Penguji 1:</span>
                        <span x-text="penguji1Nama || '— belum dipilih —'"
                              :class="penguji1Nama ? 'font-semibold text-brand-700' : 'text-slate-400 italic'"></span>
                    </div>
                    <div class="flex gap-2">
                        <span class="w-20 font-medium text-slate-500">Penguji 2:</span>
                        <span x-text="penguji2Nama || '—'"
                              :class="penguji2Nama ? 'font-semibold text-teal-700' : 'text-slate-400 italic'"></span>
                    </div>
                </div>

                <div class="mt-4 flex gap-3">
                    <button @click="if (!penguji1) { $dispatch('notify', {type:'warning', message:'Pilih dosen penguji 1 terlebih dahulu.'}); return; } modalSetujui = true"
                            class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors">
                        <x-icon name="check-circle" class="h-4 w-4" />
                        Setujui & Tetapkan Penguji
                    </button>
                    <button @click="modalTolak = true"
                            class="inline-flex items-center gap-2 rounded-xl border border-red-200 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition-colors">
                        <x-icon name="x-circle" class="h-4 w-4" />
                        Tolak
                    </button>
                </div>
                @error('dosen_penguji_id') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
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
                    <h3 class="mb-3 text-base font-bold text-slate-900">Setujui & Tetapkan Penguji Seminar</h3>
                    <p class="mb-2 text-sm text-slate-600">Dosen penguji yang ditetapkan:</p>
                    <div class="mb-4 rounded-xl bg-brand-50 px-3 py-2 text-sm space-y-1">
                        <div class="flex gap-2"><span class="w-20 text-slate-500">Penguji 1:</span><span class="font-semibold text-brand-700" x-text="penguji1Nama"></span></div>
                        <div class="flex gap-2"><span class="w-20 text-slate-500">Penguji 2:</span><span class="font-semibold text-brand-600" x-text="penguji2Nama || '—'"></span></div>
                    </div>
                    <form method="POST" action="{{ route('kaprodi.akademik.seminar.setujui', $pengajuan) }}" class="space-y-3">
                        @csrf
                        <input type="hidden" name="dosen_penguji_id"   :value="penguji1" />
                        <input type="hidden" name="dosen_penguji_2_id" :value="penguji2" />
                        <div>
                            <x-input-label for="sem_cat" value="Catatan untuk Mahasiswa (opsional)" />
                            <textarea id="sem_cat" name="catatan_kaprodi" rows="2"
                                      class="mt-1 block w-full rounded-xl border-slate-200 text-sm focus:border-brand-400 focus:ring-brand-400"
                                      placeholder="Informasi tambahan untuk mahasiswa..."></textarea>
                        </div>
                        <p class="text-xs text-slate-400">
                            <x-icon name="info" class="h-3.5 w-3.5 inline mr-1 text-sky-400" />
                            Jadwal (tanggal, waktu, tempat) akan ditetapkan oleh Admin setelah persetujuan ini.
                        </p>
                        <div class="flex justify-end gap-3 pt-1">
                            <button type="button" @click="modalSetujui = false"
                                    class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors">
                                <x-icon name="check" class="h-4 w-4" />
                                Setujui & Tetapkan Penguji
                            </button>
                        </div>
                    </form>
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
                    <h3 class="mb-2 text-base font-bold text-slate-900">Tolak Pengajuan Seminar</h3>
                    <form method="POST" action="{{ route('kaprodi.akademik.seminar.tolak', $pengajuan) }}" class="space-y-4">
                        @csrf
                        <textarea name="catatan_penolakan" rows="3" required placeholder="Berikan alasan penolakan..."
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
                    <p class="text-sm font-semibold text-emerald-800">Seminar Proposal Disetujui</p>
                </div>
                <p class="text-sm text-emerald-700">Penguji 1: <strong>{{ $pengajuan->dosenPenguji?->nama ?? '—' }}</strong></p>
                @if ($pengajuan->dosenPenguji2)
                    <p class="mt-0.5 text-sm text-emerald-700">Penguji 2: <strong>{{ $pengajuan->dosenPenguji2->nama }}</strong></p>
                @endif
                @if ($pengajuan->tanggal_jadwal)
                    <p class="mt-1 text-sm text-emerald-700">
                        Jadwal: {{ \Carbon\Carbon::parse($pengajuan->tanggal_jadwal)->locale('id')->isoFormat('dddd, D MMMM Y') }}
                        · {{ $pengajuan->waktu_jadwal }} · {{ $pengajuan->tempat_jadwal }}
                    </p>
                @endif
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

        {{-- Riwayat --}}
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
