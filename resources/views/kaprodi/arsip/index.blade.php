<x-app-layout>
    <x-slot name="title">Monitoring Surat Mahasiswa</x-slot>

    <div class="space-y-5">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Monitoring Surat Mahasiswa</h2>
            <p class="mt-0.5 text-sm text-slate-500">Pantau status surat yang sedang diproses oleh Admin. Bersifat read-only.</p>
        </div>

        {{-- Statistik ringkas --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-center">
                <p class="text-2xl font-bold text-amber-700">{{ $stats['diajukan'] }}</p>
                <p class="mt-0.5 text-xs text-amber-600">Menunggu Diproses</p>
            </div>
            <div class="rounded-xl border border-violet-200 bg-violet-50 p-4 text-center">
                <p class="text-2xl font-bold text-violet-700">{{ $stats['menunggu_ttd'] }}</p>
                <p class="mt-0.5 text-xs text-violet-600">Menunggu TTD</p>
            </div>
            <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 text-center">
                <p class="text-2xl font-bold text-sky-700">{{ $stats['sudah_ditandatangani'] }}</p>
                <p class="mt-0.5 text-xs text-sky-600">Sudah Ditandatangani</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-center">
                <p class="text-2xl font-bold text-emerald-700">{{ $stats['selesai'] }}</p>
                <p class="mt-0.5 text-xs text-emerald-600">Selesai</p>
            </div>
        </div>

        {{-- Filter --}}
        <form method="GET" action="" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Jenis Surat</label>
                <select name="jenis" onchange="this.form.submit()"
                        class="rounded-xl border-slate-200 py-1.5 pl-3 pr-8 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400">
                    <option value="">Semua Jenis</option>
                    @foreach ($jenisList as $key => $label)
                        <option value="{{ $key }}" {{ request('jenis') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Status</label>
                <select name="status" onchange="this.form.submit()"
                        class="rounded-xl border-slate-200 py-1.5 pl-3 pr-8 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400">
                    <option value="">Semua Status</option>
                    @foreach ($statusList as $key => $label)
                        <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-48">
                <label class="block text-xs font-medium text-slate-500 mb-1">Cari Mahasiswa</label>
                <div class="flex gap-2">
                    <input type="text" name="q" value="{{ request('q') }}"
                           placeholder="Nama atau NIM..."
                           class="flex-1 rounded-xl border-slate-200 py-1.5 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400" />
                    <button type="submit" class="rounded-xl bg-brand-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition-colors">
                        Cari
                    </button>
                    @if (request()->hasAny(['jenis','status','q']))
                        <a href="{{ route('kaprodi.monitoring-surat.index') }}"
                           class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs text-slate-600 hover:bg-slate-50 transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Tabel --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Mahasiswa</th>
                        <th class="px-4 py-3 text-left">Jenis Surat</th>
                        <th class="px-4 py-3 text-left">Keperluan</th>
                        <th class="px-4 py-3 text-left">Diajukan</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Terakhir Diupdate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($surat as $s)
                        @php $df = $s->data_form ?? []; @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3">
                                <p class="font-medium text-slate-800">{{ $s->mahasiswa->user->name }}</p>
                                <p class="text-xs text-slate-400">{{ $s->mahasiswa->nim }}</p>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-600">
                                {{ $jenisList[$s->jenis_surat] ?? $s->jenis_surat }}
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-500 max-w-xs">
                                @if ($s->jenis_surat === 'aktif_kuliah')
                                    {{ $df['keperluan'] ?? '—' }}
                                @elseif (in_array($s->jenis_surat, ['izin_magang','rekomendasi_magang']))
                                    {{ $df['nama_instansi'] ?? '—' }}
                                    @if (!empty($df['tanggal_mulai']))
                                        <br><span class="text-slate-400">{{ \Carbon\Carbon::parse($df['tanggal_mulai'])->format('d M Y') }} s.d. {{ \Carbon\Carbon::parse($df['tanggal_selesai'])->format('d M Y') }}</span>
                                    @endif
                                @elseif ($s->jenis_surat === 'izin_penelitian')
                                    {{ \Illuminate\Support\Str::limit($df['judul_penelitian'] ?? '—', 40) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-400 whitespace-nowrap">
                                {{ $s->created_at->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <x-status-badge :status="$s->status" />
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-400 whitespace-nowrap">
                                {{ $s->updated_at->diffForHumans() }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center">
                                <x-icon name="inbox" class="mx-auto h-8 w-8 text-slate-300 mb-2" />
                                <p class="text-sm text-slate-400">Tidak ada surat yang ditemukan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($surat->hasPages())
            <div>{{ $surat->withQueryString()->links() }}</div>
        @endif
    </div>
</x-app-layout>
