<x-app-layout>
    <x-slot name="title">Detail Mahasiswa — {{ $mahasiswa->user->name }}</x-slot>

    <div class="mb-4 flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('admin.mahasiswa.index') }}" class="hover:text-brand-600 transition-colors">Data Mahasiswa</a>
        <x-icon name="chevron-right" class="h-4 w-4 text-slate-300" />
        <span class="font-medium text-slate-700">{{ $mahasiswa->user->name }}</span>
    </div>

    <div class="space-y-5">

        {{-- Info Mahasiswa --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-brand-100 text-lg font-bold text-brand-700">
                        {{ strtoupper(substr($mahasiswa->user->name, 0, 1)) }}
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-800">{{ $mahasiswa->user->name }}</h2>
                        <p class="text-sm text-slate-500">NIM: <span class="font-mono font-medium">{{ $mahasiswa->nim }}</span></p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium {{ $mahasiswa->user->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                        {{ $mahasiswa->user->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                    <a href="{{ route('admin.mahasiswa.edit', $mahasiswa) }}"
                       class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                        <x-icon name="pencil" class="h-3.5 w-3.5" />
                        Edit
                    </a>
                </div>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                <div>
                    <p class="text-xs text-slate-400">Email</p>
                    <p class="font-medium text-slate-700">{{ $mahasiswa->user->email }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Angkatan</p>
                    <p class="font-medium text-slate-700">{{ $mahasiswa->angkatan ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Alamat</p>
                    <p class="font-medium text-slate-700">{{ $mahasiswa->alamat ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Terdaftar</p>
                    <p class="font-medium text-slate-700">{{ $mahasiswa->created_at->format('d M Y') }}</p>
                </div>
            </div>
        </div>

        {{-- Semua Berkas — terpusat dari semua pengajuan --}}
        @php
            $semuaBerkas = collect();

            // Berkas dari pengajuan judul
            foreach ($mahasiswa->pengajuanJudul as $judul) {
                foreach ($judul->berkas as $b) {
                    $semuaBerkas->push([
                        'berkas'  => $b,
                        'sumber'  => 'Judul Skripsi',
                        'label'   => $b->label,
                        'tanggal' => $b->created_at,
                    ]);
                }
            }

            // Berkas dari pengajuan surat
            foreach ($mahasiswa->pengajuanSurat as $surat) {
                $jenisList = [
                    'seminar_proposal'   => 'Seminar Proposal',
                    'sidang_skripsi'     => 'Sidang Skripsi',
                    'aktif_kuliah'       => 'Aktif Kuliah',
                    'izin_magang'        => 'Izin Magang',
                    'rekomendasi_magang' => 'Rekomendasi Magang',
                    'izin_penelitian'    => 'Izin Penelitian',
                ];
                $jenisLabel = $jenisList[$surat->jenis_surat] ?? $surat->jenis_surat;
                foreach ($surat->berkas as $b) {
                    $semuaBerkas->push([
                        'berkas'  => $b,
                        'sumber'  => $jenisLabel,
                        'label'   => $b->label,
                        'tanggal' => $b->created_at,
                    ]);
                }
            }

            $semuaBerkas = $semuaBerkas->sortByDesc('tanggal');
        @endphp

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-slate-800">
                <x-icon name="paperclip" class="h-4 w-4 text-slate-400" />
                Semua Berkas yang Pernah Diupload
                <span class="ml-auto text-xs font-normal text-slate-400">{{ $semuaBerkas->count() }} file</span>
            </h3>

            @if ($semuaBerkas->isEmpty())
                <div class="rounded-xl bg-slate-50 py-8 text-center">
                    <x-icon name="file-x" class="mx-auto h-8 w-8 text-slate-300 mb-2" />
                    <p class="text-sm text-slate-400">Mahasiswa belum pernah mengupload berkas apapun.</p>
                </div>
            @else
                <div class="overflow-hidden rounded-xl border border-slate-100">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-4 py-2.5 text-left">Nama File</th>
                                <th class="px-4 py-2.5 text-left">Label</th>
                                <th class="px-4 py-2.5 text-left">Dari Pengajuan</th>
                                <th class="px-4 py-2.5 text-left">Diupload</th>
                                <th class="px-4 py-2.5 text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($semuaBerkas as $item)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-2.5 text-xs">
                                        <div class="flex items-center gap-2">
                                            <x-icon name="file" class="h-3.5 w-3.5 shrink-0 text-slate-400" />
                                            <span class="max-w-xs truncate text-slate-700">{{ $item['berkas']->nama_asli }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2.5 text-xs text-slate-500">{{ $item['label'] }}</td>
                                    <td class="px-4 py-2.5">
                                        <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600">
                                            {{ $item['sumber'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2.5 text-xs text-slate-400 whitespace-nowrap">
                                        {{ $item['tanggal']->format('d M Y') }}
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <a href="{{ route('admin.berkas.download', $item['berkas']) }}"
                                           class="inline-flex items-center gap-1 rounded-lg border border-brand-200 bg-brand-50 px-2.5 py-1 text-xs font-medium text-brand-600 hover:bg-brand-100 transition-colors">
                                            <x-icon name="download" class="h-3 w-3" />
                                            Download
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Riwayat Pengajuan Akademik --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-slate-800">
                <x-icon name="graduation-cap" class="h-4 w-4 text-slate-400" />
                Riwayat Pengajuan Akademik
            </h3>

            @forelse ($mahasiswa->pengajuanJudul as $judul)
                <div class="mb-3 rounded-xl border border-slate-100 bg-slate-50 p-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-slate-800 leading-snug">{{ $judul->judul }}</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">{{ $judul->bidang_kajian }} · {{ $judul->created_at->format('d M Y') }}</p>
                            @if ($judul->dosenPembimbing)
                                <p class="text-[10px] text-brand-600 mt-0.5">Pembimbing: {{ $judul->dosenPembimbing->nama }}</p>
                            @endif
                        </div>
                        <x-status-badge :status="$judul->status" />
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-400">Belum ada pengajuan judul.</p>
            @endforelse
        </div>

        {{-- Riwayat Surat --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-slate-800">
                <x-icon name="mail" class="h-4 w-4 text-slate-400" />
                Riwayat Surat
            </h3>

            @php
                $jenisSuratList = [
                    'seminar_proposal'   => 'Seminar Proposal',
                    'sidang_skripsi'     => 'Sidang Skripsi',
                    'aktif_kuliah'       => 'Aktif Kuliah',
                    'izin_magang'        => 'Izin Magang',
                    'rekomendasi_magang' => 'Rekomendasi Magang',
                    'izin_penelitian'    => 'Izin Penelitian',
                ];
            @endphp

            @forelse ($mahasiswa->pengajuanSurat->sortByDesc('created_at') as $surat)
                <div class="mb-2 flex items-center justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50 px-3 py-2.5">
                    <div class="flex items-center gap-3 min-w-0">
                        <x-icon name="file-text" class="h-4 w-4 shrink-0 text-slate-400" />
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-slate-700">{{ $jenisSuratList[$surat->jenis_surat] ?? $surat->jenis_surat }}</p>
                            <p class="text-[10px] text-slate-400">{{ $surat->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <x-status-badge :status="$surat->status" />
                        @if ($surat->berkas->count())
                            <span class="text-[10px] text-slate-400">{{ $surat->berkas->count() }} berkas</span>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-400">Belum ada pengajuan surat.</p>
            @endforelse
        </div>

    </div>
</x-app-layout>
