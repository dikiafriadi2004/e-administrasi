<x-app-layout>
    <x-slot name="title">Ajukan Sidang Skripsi</x-slot>
    <div class="mb-4 flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('mahasiswa.riwayat.index') }}" class="hover:text-brand-600">Riwayat</a>
        <span>/</span><span class="text-gray-700">Sidang Skripsi</span>
    </div>
    <div class="max-w-2xl mx-auto">
        <div class="rounded-xl border bg-white p-5 shadow-sm">
            <h2 class="mb-1 text-sm font-semibold text-slate-800">Pengajuan Sidang Skripsi</h2>
            <p class="mb-4 text-xs text-slate-500">Upload setiap berkas persyaratan secara terpisah sesuai ketentuan prodi.</p>

            {{-- Info dari judul --}}
            <div class="mb-4 rounded-xl border border-brand-100 bg-brand-50 px-4 py-3 text-xs space-y-1.5">
                <p class="font-semibold text-brand-700 mb-1">Data dari Judul yang Disetujui</p>
                <div class="flex gap-2">
                    <span class="w-24 shrink-0 text-slate-500">Judul:</span>
                    <span class="text-slate-800 leading-snug">{{ $pengajuanJudul->judul }}</span>
                </div>
                <div class="flex gap-2">
                    <span class="w-24 shrink-0 text-slate-500">Pembimbing:</span>
                    <span class="text-slate-800">{{ $pengajuanJudul->dosenPembimbing?->nama ?? '—' }}</span>
                </div>
            </div>

            {{-- Info jadwal --}}
            <div class="mb-4 rounded-xl border border-sky-100 bg-sky-50 px-4 py-3 text-xs">
                <div class="flex items-start gap-2">
                    <x-icon name="info" class="h-3.5 w-3.5 shrink-0 text-sky-500 mt-0.5" />
                    <p class="text-sky-800">
                        Admin akan memverifikasi kelengkapan berkas, lalu Kaprodi ACC, dan Admin menetapkan jadwal sidang.
                    </p>
                </div>
            </div>

            @if ($errors->any())
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <p class="font-semibold mb-1">Periksa isian berikut:</p>
                    <ul class="list-disc list-inside space-y-0.5 text-xs">
                        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                    </ul>
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('mahasiswa.pengajuan.sidang.store') }}"
                  enctype="multipart/form-data" class="space-y-5">
                @csrf

                {{-- Usulan Tanggal --}}
                <div>
                    <x-input-label for="tanggalRencana" value="Usulan Tanggal Sidang (opsional)" />
                    <p class="mb-1 text-[10px] text-slate-400">Admin akan mempertimbangkan usulan ini.</p>
                    <x-text-input id="tanggalRencana" name="tanggalRencana" type="date"
                                  class="mt-1 block w-full" value="{{ old('tanggalRencana') }}"
                                  min="{{ now()->addDay()->format('Y-m-d') }}" />
                    @error('tanggalRencana') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <hr class="border-slate-100">
                <p class="text-xs font-semibold text-slate-600">Berkas Persyaratan Sidang Skripsi</p>
                <p class="text-xs text-slate-400 -mt-3">Upload setiap berkas dalam format PDF, DOC, atau DOCX · Maks 10 MB per file. Tandai <span class="text-red-500">*</span> = wajib.</p>

                @php
                $berkasList = [
                    ['key' => 'surat_permohonan',      'label' => 'Surat Permohonan',                           'wajib' => true],
                    ['key' => 'biodata_mahasiswa',      'label' => 'Biodata Mahasiswa',                          'wajib' => true],
                    ['key' => 'lembar_persetujuan',     'label' => 'Lembar Persetujuan Skripsi',                 'wajib' => true],
                    ['key' => 'kwitansi_spp',           'label' => 'Kwitansi SPP Terakhir',                      'wajib' => true],
                    ['key' => 'transkip_nilai',         'label' => 'Transkip Nilai (disahkan WD1)',               'wajib' => true],
                    ['key' => 'khs',                    'label' => 'Kartu Hasil Studi (KHS) Semester 1 s/d Akhir', 'wajib' => true],
                    ['key' => 'naskah_skripsi',         'label' => 'Naskah Skripsi (4 eksemplar — scan/PDF)',    'wajib' => true],
                    ['key' => 'ket_hadir_seminar',      'label' => 'Keterangan Hadir Seminar Minimal 10 Kali',   'wajib' => true],
                    ['key' => 'buku_bimbingan',         'label' => 'Buku Bimbingan Skripsi',                     'wajib' => true],
                    ['key' => 'krs_terakhir',           'label' => 'Kartu Rencana Studi (KRS) Terakhir',         'wajib' => true],
                    ['key' => 'abstrak_skripsi',        'label' => 'Abstrak Skripsi',                            'wajib' => true],
                    ['key' => 'nilai_toefl',            'label' => 'Nilai TOEFL',                                'wajib' => true],
                    ['key' => 'sk_pembimbing',          'label' => 'SK Pembimbing Mahasiswa',                    'wajib' => true],
                    ['key' => 'jurnal_ilmiah',          'label' => 'Jurnal Ilmiah Mahasiswa',                    'wajib' => true],
                    ['key' => 'map_berwarna',           'label' => 'Map Berwarna Merah (foto/scan)',              'wajib' => false],
                    ['key' => 'bebas_turnitin',         'label' => 'Surat Keterangan Bebas Turnitin ≤30%',       'wajib' => true],
                ];
                @endphp

                <div class="space-y-3">
                    @foreach ($berkasList as $idx => $item)
                        <div class="rounded-xl border {{ $item['wajib'] ? 'border-slate-200 bg-white' : 'border-slate-100 bg-slate-50' }} p-3">
                            <label for="berkas_{{ $item['key'] }}" class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ $idx + 1 }}. {{ $item['label'] }}
                                @if ($item['wajib']) <span class="text-red-500 ml-0.5">*</span> @else <span class="text-slate-400 font-normal">(opsional)</span> @endif
                            </label>
                            <input id="berkas_{{ $item['key'] }}"
                                   name="berkas[{{ $item['key'] }}]"
                                   type="file"
                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                   {{ $item['wajib'] ? 'required' : '' }}
                                   class="block w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-xs
                                          file:mr-2 file:rounded-lg file:border-0 file:bg-brand-50 file:px-2 file:py-0.5
                                          file:text-xs file:font-medium file:text-brand-700 hover:file:bg-brand-100" />
                            @error("berkas.{$item['key']}") <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                </div>

                <div class="flex flex-col gap-2 pt-2">
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600 transition-colors">
                        Kirim Pengajuan Sidang
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
