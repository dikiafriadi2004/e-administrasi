<x-app-layout>
    <x-slot name="title">Riwayat Pengajuan</x-slot>

    <div class="space-y-6">

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-700">Ajukan Baru</h3>
                <x-per-page-selector :current="$perPage" />
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('mahasiswa.pengajuan.judul.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-brand-200 bg-brand-50 px-3 py-1.5 text-xs font-medium text-brand-700 hover:bg-brand-100 transition-colors">
                    <x-icon name="file-text" class="h-3.5 w-3.5" />
                    Judul Skripsi
                </a>
                <a href="{{ route('mahasiswa.pengajuan.aktif-kuliah.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-sky-200 bg-sky-50 px-3 py-1.5 text-xs font-medium text-sky-700 hover:bg-sky-100 transition-colors">
                    <x-icon name="clipboard-list" class="h-3.5 w-3.5" />
                    Surat Aktif Kuliah
                </a>
                <a href="{{ route('mahasiswa.pengajuan.seminar.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-100 transition-colors">
                    <x-icon name="presentation" class="h-3.5 w-3.5" />
                    Seminar Proposal
                </a>
                <a href="{{ route('mahasiswa.pengajuan.sidang.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-violet-200 bg-violet-50 px-3 py-1.5 text-xs font-medium text-violet-700 hover:bg-violet-100 transition-colors">
                    <x-icon name="landmark" class="h-3.5 w-3.5" />
                    Sidang Skripsi
                </a>
                <a href="{{ route('mahasiswa.pengajuan.izin-penelitian.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-teal-200 bg-teal-50 px-3 py-1.5 text-xs font-medium text-teal-700 hover:bg-teal-100 transition-colors">
                    <x-icon name="flask-conical" class="h-3.5 w-3.5" />
                    Izin Penelitian
                </a>
            </div>
        </div>

        <div class="space-y-2">
            <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50">
                    <x-icon name="graduation-cap" class="h-4 w-4 text-brand-600" />
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-800">Riwayat Pengajuan Akademik</h2>
                    <p class="text-xs text-slate-400">Judul skripsi, seminar proposal, dan sidang skripsi. Diputuskan langsung oleh Kaprodi.</p>
                </div>
            </div>

            {{-- Judul --}}
            <div class="overflow-hidden rounded-xl border bg-white shadow-sm">
                <div class="border-b bg-slate-50 px-4 py-2.5 flex items-center gap-2">
                    <x-icon name="file-text" class="h-3.5 w-3.5 text-slate-500" />
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Judul Skripsi</span>
                </div>
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Judul</th>
                            <th class="px-4 py-3 text-left">Bidang</th>
                            <th class="px-4 py-3 text-left">Pembimbing</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($pengajuanJudul as $judul)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="max-w-xs px-4 py-3">
                                    <p class="truncate font-medium text-gray-800" title="{{ $judul->judul }}">{{ $judul->judul }}</p>
                                    <p class="text-xs text-slate-400">{{ $judul->created_at->format('d M Y') }}</p>
                                </td>
                                <td class="px-4 py-3 text-gray-500 text-xs">{{ $judul->bidang_kajian }}</td>
                                <td class="px-4 py-3 text-gray-700 text-xs">
                                    @if ($judul->dosenPembimbing)
                                        <p>1. {{ $judul->dosenPembimbing->nama }}</p>
                                    @endif
                                    @if ($judul->dosenPembimbing2)
                                        <p class="text-slate-500">2. {{ $judul->dosenPembimbing2->nama }}</p>
                                    @endif
                                    @if (! $judul->dosenPembimbing)
                                        <span class="text-gray-400 italic">Belum ditentukan</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3"><x-status-badge :status="$judul->status" /></td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('mahasiswa.pengajuan.judul.show', $judul) }}"
                                           class="rounded px-2 py-1 text-xs font-medium text-brand-600 hover:bg-brand-50">Detail</a>
                                        @if (in_array($judul->status, ['diajukan', 'ditolak']))
                                            <a href="{{ route('mahasiswa.pengajuan.judul.edit', $judul) }}"
                                               class="inline-flex items-center gap-1 rounded px-2 py-1 text-xs font-medium text-amber-600 hover:bg-amber-50">
                                                <x-icon name="pencil" class="h-3 w-3" />
                                                Revisi
                                            </a>
                                        @endif
                                        @if ($judul->status === 'disetujui')
                                            @php
                                                $sidangAktif = \App\Models\PengajuanSurat::where('mahasiswa_id', auth()->user()->mahasiswa?->id)
                                                    ->where('jenis_surat', 'sidang_skripsi')
                                                    ->whereNotIn('status', ['ditolak'])
                                                    ->exists();
                                            @endphp
                                            @if (!$sidangAktif)
                                                <a href="{{ route('mahasiswa.pengajuan.judul.edit', $judul) }}"
                                                   class="inline-flex items-center gap-1 rounded px-2 py-1 text-xs font-medium text-orange-600 hover:bg-orange-50"
                                                   title="Revisi judul setelah seminar">
                                                    <x-icon name="pencil" class="h-3 w-3" />
                                                    Revisi Judul
                                                </a>
                                            @endif
                                        @endif
                                    </div>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-4 text-center text-sm text-gray-400">Belum ada pengajuan judul.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($pengajuanJudul->hasPages())
                <div class="px-4">{{ $pengajuanJudul->links() }}</div>
            @endif

            {{-- Seminar --}}
            <div class="overflow-hidden rounded-xl border bg-white shadow-sm">
                <div class="border-b bg-slate-50 px-4 py-2.5 flex items-center gap-2">
                    <x-icon name="presentation" class="h-3.5 w-3.5 text-slate-500" />
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Seminar Proposal</span>
                </div>
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Rencana Tanggal</th>
                            <th class="px-4 py-3 text-left">Penguji / Jadwal</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Unduhan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($pengajuanSeminar as $s)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                    @if ($s->tanggal_jadwal)
                                        <p class="font-medium">{{ $s->tanggal_jadwal->locale('id')->isoFormat('D MMM Y') }}</p>
                                    @else
                                        <p class="text-slate-400 italic">{{ $s->data_form['tanggal_rencana'] ?? '-' }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-700">
                                    @if ($s->dosenPenguji)
                                        <p>Penguji I: {{ $s->dosenPenguji->nama }}</p>
                                    @endif
                                    @if ($s->dosenPenguji2)
                                        <p class="text-slate-500">Penguji II: {{ $s->dosenPenguji2->nama }}</p>
                                    @endif
                                    @if ($s->waktu_jadwal)
                                        <p class="mt-0.5 text-slate-400">{{ $s->waktu_jadwal }} — {{ $s->tempat_jadwal }}</p>
                                    @endif
                                    @if (! $s->dosenPenguji && ! $s->waktu_jadwal)
                                        <span class="text-slate-400 italic">Menunggu Kaprodi</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3"><x-status-badge :status="$s->status" /></td>
                                <td class="px-4 py-3">
                                    @if ($s->file_scan)
                                        <a href="{{ route('mahasiswa.surat.download', [$s, 'scan']) }}"
                                           class="inline-flex items-center gap-1.5 rounded-lg border border-brand-200 bg-brand-50 px-2.5 py-1 text-xs font-medium text-brand-700 hover:bg-brand-100 transition-colors">
                                            <x-icon name="download" class="h-3.5 w-3.5" />
                                            Undangan
                                        </a>
                                    @elseif ($s->status === 'ditolak')
                                        <span class="text-xs text-red-500 italic">Ditolak</span>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Belum tersedia</span>
                                    @endif
                                </td>
                            </tr>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-4 text-center text-sm text-gray-400">Belum ada pengajuan seminar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($pengajuanSeminar->hasPages())
                <div class="px-4">{{ $pengajuanSeminar->links() }}</div>
            @endif

            {{-- Sidang --}}
            <div class="overflow-hidden rounded-xl border bg-white shadow-sm">
                <div class="border-b bg-slate-50 px-4 py-2.5 flex items-center gap-2">
                    <x-icon name="landmark" class="h-3.5 w-3.5 text-slate-500" />
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Sidang Skripsi</span>
                </div>
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Jadwal / Tanggal</th>
                            <th class="px-4 py-3 text-left">Penguji</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Unduhan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($pengajuanSidang as $s)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                    @if ($s->tanggal_jadwal)
                                        <p class="font-medium">{{ $s->tanggal_jadwal->locale('id')->isoFormat('D MMM Y') }}</p>
                                        @if ($s->waktu_jadwal)
                                            <p class="text-xs text-slate-400">{{ $s->waktu_jadwal }} &mdash; {{ $s->tempat_jadwal }}</p>
                                        @endif
                                    @elseif (! empty($s->data_form['tanggal_rencana']))
                                        <p class="text-xs text-slate-400">Usul: {{ $s->data_form['tanggal_rencana'] }}</p>
                                    @else
                                        <p class="text-slate-400 italic text-xs">Menunggu jadwal</p>
                                    @endif
                                    {{-- Tampilkan catatan admin jika berkas dikembalikan --}}
                                    @if ($s->catatan_admin && ! $s->berkas_diverifikasi)
                                        <div class="mt-1 rounded-lg border border-red-200 bg-red-50 px-2 py-1 text-[10px] text-red-700">
                                            <span class="font-semibold">Berkas dikembalikan:</span> {{ $s->catatan_admin }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-700">
                                    @if ($s->dosenPenguji)
                                        <p>I. {{ $s->dosenPenguji->nama }}</p>
                                    @endif
                                    @if ($s->dosenPenguji2)
                                        <p class="text-slate-500">II. {{ $s->dosenPenguji2->nama }}</p>
                                    @endif
                                    @if (! $s->dosenPenguji)
                                        <span class="text-slate-400 italic">Menunggu Kaprodi</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3"><x-status-badge :status="$s->status" /></td>
                                <td class="px-4 py-3">
                                    @if ($s->file_scan)
                                        <a href="{{ route('mahasiswa.surat.download', [$s, 'scan']) }}"
                                           class="inline-flex items-center gap-1.5 rounded-lg border border-brand-200 bg-brand-50 px-2.5 py-1 text-xs font-medium text-brand-700 hover:bg-brand-100 transition-colors">
                                            <x-icon name="download" class="h-3.5 w-3.5" />
                                            Undangan
                                        </a>
                                    @elseif ($s->status === 'ditolak')
                                        <span class="text-xs text-red-500 italic">Ditolak</span>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Belum tersedia</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-4 text-center text-sm text-gray-400">Belum ada pengajuan sidang.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($pengajuanSidang->hasPages())
                <div class="px-4">{{ $pengajuanSidang->links() }}</div>
            @endif
        </div>

        <div class="space-y-2">
            <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-50">
                    <x-icon name="clipboard-list" class="h-4 w-4 text-sky-600" />
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-800">Riwayat Surat</h2>
                    <p class="text-xs text-slate-400">Surat aktif kuliah dan surat lainnya. Admin memproses dan upload scan setelah TTD Kaprodi.</p>
                </div>
            </div>
            <div class="overflow-hidden rounded-xl border bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Jenis Surat</th>
                            <th class="px-4 py-3 text-left">Keperluan / Tujuan</th>
                            <th class="px-4 py-3 text-left">Nomor Surat</th>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Download</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($pengajuanSurat as $surat)
                            @php
                                $jenisList = [
                                    'aktif_kuliah'       => 'Aktif Kuliah',
                                    'undangan_penguji'   => 'Undangan Penguji',
                                    'izin_magang'        => 'Izin Magang / PKL',
                                    'rekomendasi_magang' => 'Rekomendasi Magang',
                                    'izin_penelitian'    => 'Izin Penelitian',
                                ];
                                $adaScan = (bool) $surat->file_scan;
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3 font-medium text-gray-800">
                                    {{ $jenisList[$surat->jenis_surat] ?? $surat->jenis_surat }}
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500">
                                    @if ($surat->jenis_surat === 'aktif_kuliah')
                                        <p>{{ $surat->data_form['keperluan'] ?? '-' }}</p>
                                        @if (! empty($surat->data_form['tujuan_instansi']))
                                            <p class="text-slate-400">{{ $surat->data_form['tujuan_instansi'] }}</p>
                                        @endif
                                    @elseif (in_array($surat->jenis_surat, ['izin_magang', 'rekomendasi_magang']))
                                        <p>{{ $surat->data_form['nama_instansi'] ?? '-' }}</p>
                                        @if (! empty($surat->data_form['tanggal_mulai']))
                                            <p class="text-slate-400">
                                                {{ \Carbon\Carbon::parse($surat->data_form['tanggal_mulai'])->format('d M Y') }}
                                                s.d.
                                                {{ \Carbon\Carbon::parse($surat->data_form['tanggal_selesai'])->format('d M Y') }}
                                            </p>
                                        @endif
                                        @if ($surat->berkas->count())
                                            <p class="mt-0.5 text-slate-400 italic">{{ $surat->berkas->count() }} berkas dilampirkan</p>
                                        @endif
                                    @elseif ($surat->jenis_surat === 'izin_penelitian')
                                        <p>{{ \Illuminate\Support\Str::limit($surat->data_form['judul_penelitian'] ?? '-', 50) }}</p>
                                        @if (! empty($surat->data_form['nama_instansi']))
                                            <p class="text-slate-400">{{ $surat->data_form['nama_instansi'] }}</p>
                                        @endif
                                        @if (! empty($surat->data_form['tanggal_mulai']))
                                            <p class="text-slate-400">
                                                {{ \Carbon\Carbon::parse($surat->data_form['tanggal_mulai'])->format('d M Y') }}
                                                s.d.
                                                {{ \Carbon\Carbon::parse($surat->data_form['tanggal_selesai'])->format('d M Y') }}
                                            </p>
                                        @endif
                                        @if ($surat->berkas->count())
                                            <p class="mt-0.5 text-slate-400 italic">{{ $surat->berkas->count() }} berkas dilampirkan</p>
                                        @endif
                                    @else
                                        {{ $surat->data_form['tanggal_rencana'] ?? '-' }}
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-mono text-xs text-gray-500">
                                    {{ $surat->nomor_surat ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-500">
                                    {{ $surat->created_at->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3">
                                    <x-status-badge :status="$surat->status" />
                                </td>
                                <td class="px-4 py-3">
                                    @if ($adaScan)
                                        <a href="{{ route('mahasiswa.surat.download', [$surat, 'scan']) }}"
                                           class="inline-flex items-center gap-1.5 rounded-lg border border-brand-200 bg-brand-50 px-2.5 py-1 text-xs font-medium text-brand-700 hover:bg-brand-100 transition-colors">
                                            <x-icon name="download" class="h-3.5 w-3.5" />
                                            Download Surat
                                        </a>
                                    @elseif ($surat->status === 'ditolak')
                                        <span class="text-xs text-red-500 italic">Ditolak</span>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Belum tersedia</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-400">
                                    Belum ada pengajuan surat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($pengajuanSurat->hasPages())
                <div class="px-4">{{ $pengajuanSurat->links() }}</div>
            @endif
        </div>

    </div>
</x-app-layout>

