<x-app-layout>
    <x-slot name="title">Arsip Semua Surat</x-slot>

    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-800">Arsip Surat</h2>
                <p class="text-sm text-gray-500">Total: {{ $surat->total() }} surat</p>
            </div>
            <a href="{{ route('admin.arsip.export', request()->only(['jenis','status','q','dari','sampai'])) }}"
               class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 hover:bg-emerald-100 transition-colors">
                <x-icon name="download" class="h-4 w-4" />
                Export Excel
            </a>
        </div>

        {{-- Filter --}}
        <form method="GET" action="{{ route('admin.arsip.index') }}"
              class="rounded-xl border bg-white p-4 shadow-sm">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5">
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="Cari NIM, nama, nomor surat..."
              class="col-span-1 rounded-xl border-slate-200 text-sm focus:border-brand-400 focus:ring-brand-400 xl:col-span-2" />
                <select name="jenis"
                        class="rounded-xl border-slate-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    <option value="">Semua Jenis</option>
                    @foreach ($jenisList as $k => $v)
                        <option value="{{ $k }}" {{ request('jenis') === $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
                <select name="status"
                        class="rounded-xl border-slate-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    <option value="">Semua Status</option>
                    @foreach ($statusList as $k => $v)
                        <option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
                <div class="flex gap-2">
                    <input type="date" name="dari" value="{{ request('dari') }}"
                           class="w-full rounded-xl border-slate-200 text-sm focus:border-brand-400 focus:ring-brand-400" />
                    <input type="date" name="sampai" value="{{ request('sampai') }}"
                           class="w-full rounded-xl border-slate-200 text-sm focus:border-brand-400 focus:ring-brand-400" />
                </div>
            </div>
            <div class="mt-3 flex gap-2">
                <button type="submit"
                        class="rounded-xl bg-brand-500 px-4 py-1.5 text-sm font-semibold text-white hover:bg-brand-600 transition-colors">
                    Filter
                </button>
                <a href="{{ route('admin.arsip.index') }}"
                   class="rounded-xl border border-slate-200 px-4 py-1.5 text-sm text-slate-600 hover:bg-slate-50 transition-colors">
                    Reset
                </a>
            </div>
        </form>

        {{-- Tabel --}}

        <div class="mb-2 flex items-center justify-between gap-3">
            <x-per-page-selector :current="$perPage ?? 10" />
            <p class="text-xs text-slate-400">Total: {{ $surat->total() }} data</p>
        </div>
        <div class="overflow-hidden rounded-xl border bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Mahasiswa</th>
                        <th class="px-4 py-3 text-left">Jenis Surat</th>
                        <th class="px-4 py-3 text-left">Nomor Surat</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Download</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($surat as $s)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3">
                                <p class="font-medium text-slate-800">{{ $s->mahasiswa->user->name }}</p>
                                <p class="text-xs text-gray-400">{{ $s->mahasiswa->nim }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $jenisList[$s->jenis_surat] ?? $s->jenis_surat }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $s->nomor_surat ?? 'â€”' }}</td>
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $s->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-3"><x-status-badge :status="$s->status" /></td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @if ($s->file_docx)
                                        <a href="{{ route('admin.surat.download', [$s, 'docx']) }}"
                                           class="rounded border px-2 py-0.5 text-xs text-gray-600 hover:bg-slate-50 transition-colors">DOCX</a>
                                    @endif
                                    @if ($s->file_pdf)
                                        <a href="{{ route('admin.surat.download', [$s, 'pdf']) }}"
                                           class="rounded border border-red-200 px-2 py-0.5 text-xs text-red-600 hover:bg-red-50">PDF</a>
                                    @endif
                                    @if ($s->file_scan)
                                        <a href="{{ route('admin.surat.download', [$s, 'scan']) }}"
                                           class="rounded border border-green-200 bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 hover:bg-green-100">Scan</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-400">Tidak ada data sesuai filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($surat->hasPages())
            <div>{{ $surat->links() }}</div>
        @endif
    </div>
</x-app-layout>

