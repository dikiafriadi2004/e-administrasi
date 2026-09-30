<x-app-layout>
    <x-slot name="title">Rasio Dosen</x-slot>

    <div class="space-y-4">

        {{-- Header + filter tahun --}}
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Dashboard Rasio Dosen</h2>
                <p class="text-sm text-slate-500">
                    Beban bimbingan & penugasan penguji per dosen — tahun akademik
                    <span class="font-semibold text-slate-700">{{ $tahunDipilih }}</span>.
                    @if ($tahunDipilih !== $tahunAktif)
                        <span class="ml-1 inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">
                            <x-icon name="clock" class="h-3 w-3" /> Data historis
                        </span>
                    @else
                        <span class="ml-1 inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">
                            <x-icon name="check-circle" class="h-3 w-3" /> Tahun aktif
                        </span>
                    @endif
                </p>
            </div>

            {{-- Dropdown pilih tahun --}}
            <form method="GET" action="" class="flex items-center gap-2">
                <label class="text-xs font-medium text-slate-500">Tahun Akademik:</label>
                <select name="tahun" onchange="this.form.submit()"
                    class="rounded-lg border-slate-200 py-1.5 pl-3 pr-8 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400">
                    @foreach ($tahunTersedia as $ta)
                        <option value="{{ $ta }}" {{ $ta === $tahunDipilih ? 'selected' : '' }}>
                            {{ $ta }}{{ $ta === $tahunAktif ? ' (Aktif)' : '' }}
                        </option>
                    @endforeach
                    {{-- Jika belum ada data sama sekali --}}
                    @if (empty($tahunTersedia))
                        <option value="{{ $tahunAktif }}" selected>{{ $tahunAktif }} (Aktif)</option>
                    @endif
                </select>
            </form>
        </div>

        {{-- Info reset tahunan --}}
        <div class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-xs text-blue-700">
            <span class="font-semibold">Info:</span>
            Rasio dihitung dari pengajuan yang dibuat dalam rentang tahun akademik yang dipilih
            (Agustus {{ explode('/', $tahunDipilih)[0] }} – Juli {{ explode('/', $tahunDipilih)[1] ?? (explode('/', $tahunDipilih)[0]+1) }}).
            Setiap tahun akademik baru, rasio mulai dari nol. Data tahun sebelumnya tetap bisa dilihat lewat filter di atas.
        </div>

        {{-- Chart Bar Rasio Dosen --}}
        @if ($rasio->isNotEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="text-sm font-semibold text-slate-700">Visualisasi Beban per Dosen</h3>
                <div class="flex items-center gap-3 text-xs text-slate-400">
                    <span class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5 rounded-sm bg-teal-500"></span>Bimbingan</span>
                    <span class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5 rounded-sm bg-sky-400"></span>Penguji I</span>
                    <span class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5 rounded-sm bg-violet-400"></span>Penguji II</span>
                </div>
            </div>
            <div style="position:relative; height:280px;">
                <canvas id="rasioChart"></canvas>
            </div>
        </div>
        @endif

        {{-- Tabel rasio --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3 text-left">Nama Dosen</th>
                        <th class="px-5 py-3 text-left">NIP</th>
                        <th class="px-5 py-3 text-center">Bimbingan</th>
                        <th class="px-5 py-3 text-center">Penguji I</th>
                        <th class="px-5 py-3 text-center">Penguji II</th>
                        <th class="px-5 py-3 text-center">Total Penguji</th>
                        <th class="px-5 py-3 text-center">Kapasitas Maks</th>
                        <th class="px-5 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($rasio as $dosen)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $dosen->nama }}</td>
                            <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ $dosen->nip }}</td>
                            <td class="px-5 py-3 text-center">
                                <span class="font-bold {{ $dosen->jumlah_bimbingan > 0 ? 'text-brand-600' : 'text-slate-300' }}">
                                    {{ $dosen->jumlah_bimbingan }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="font-bold {{ $dosen->jumlah_penguji_1 > 0 ? 'text-sky-600' : 'text-slate-300' }}">
                                    {{ $dosen->jumlah_penguji_1 }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="font-bold {{ $dosen->jumlah_penguji_2 > 0 ? 'text-violet-600' : 'text-slate-300' }}">
                                    {{ $dosen->jumlah_penguji_2 }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="font-bold {{ $dosen->jumlah_pengujian > 0 ? 'text-indigo-600' : 'text-slate-300' }}">
                                    {{ $dosen->jumlah_pengujian }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-center text-slate-500">{{ $dosen->kapasitas_maksimal ?? '∞' }}</td>
                            <td class="px-5 py-3 text-center">
                                @if ($dosen->isKapasitasPenuh())
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-400 shrink-0"></span>Penuh
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 shrink-0"></span>Tersedia
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-10 text-center">
                                <div class="flex flex-col items-center gap-2 text-slate-400">
                                    <x-icon name="users" class="h-8 w-8" />
                                    <p class="text-sm">Belum ada data dosen untuk tahun akademik {{ $tahunDipilih }}.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($rasio->isNotEmpty())
                    <tfoot class="border-t bg-slate-50 text-xs font-semibold text-slate-500">
                        <tr>
                            <td colspan="2" class="px-5 py-2.5 text-right">Total</td>
                            <td class="px-5 py-2.5 text-center text-brand-600">{{ $rasio->sum('jumlah_bimbingan') }}</td>
                            <td class="px-5 py-2.5 text-center text-sky-600">{{ $rasio->sum('jumlah_penguji_1') }}</td>
                            <td class="px-5 py-2.5 text-center text-violet-600">{{ $rasio->sum('jumlah_penguji_2') }}</td>
                            <td class="px-5 py-2.5 text-center text-indigo-600">{{ $rasio->sum('jumlah_pengujian') }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

    </div>
</x-app-layout>
@if ($rasio->isNotEmpty())
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('rasioChart');
    if (!ctx) return;

    const fullNames = @json($rasio->pluck('nama')->values());
    const labels    = fullNames.map(n => n.length > 22 ? n.substring(0, 22) + '\u2026' : n);
    const bimbingan = @json($rasio->pluck('jumlah_bimbingan')->values());
    const penguji1  = @json($rasio->pluck('jumlah_penguji_1')->values());
    const penguji2  = @json($rasio->pluck('jumlah_penguji_2')->values());

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'Bimbingan', data: bimbingan, backgroundColor: 'rgba(20,184,166,0.85)', borderColor: 'rgba(15,118,110,1)', borderWidth:1, borderRadius:4 },
                { label: 'Penguji I', data: penguji1,  backgroundColor: 'rgba(56,189,248,0.85)', borderColor: 'rgba(14,165,233,1)', borderWidth:1, borderRadius:4 },
                { label: 'Penguji II', data: penguji2, backgroundColor: 'rgba(167,139,250,0.85)',borderColor: 'rgba(139,92,246,1)', borderWidth:1, borderRadius:4 },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        title: (items) => fullNames[items[0].dataIndex] ?? items[0].label,
                        label: (item)  => ` ${item.dataset.label}: ${item.raw} mahasiswa`,
                    },
                },
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size:11 }, maxRotation:30, color:'#64748b' } },
                y: { beginAtZero: true, ticks: { stepSize:1, precision:0, color:'#64748b', font:{size:11} }, grid: { color:'rgba(0,0,0,0.05)' } },
            },
        },
    });
});
</script>
@endpush
@endif