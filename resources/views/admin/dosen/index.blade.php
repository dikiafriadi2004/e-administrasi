<x-app-layout>
    <x-slot name="title">Data Dosen</x-slot>

    <div class="space-y-4">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Daftar Dosen</h2>
                <p class="text-sm text-slate-500">Total: {{ $dosens->total() }} dosen</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.dosen.import.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                    <x-icon name="upload" class="h-4 w-4" />
                    Import Excel
                </a>
                <a href="{{ route('admin.dosen.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600 transition-colors">
                    <x-icon name="user-plus" class="h-4 w-4" />
                    Tambah Dosen
                </a>
            </div>
        </div>

        {{-- Tabel --}}

        <div class="mb-2 flex items-center justify-between gap-3">
            <x-per-page-selector :current="$perPage ?? 10" />
            <p class="text-xs text-slate-400">Total: {{ $dosens->total() }} data</p>
        </div>
        <div class="overflow-hidden rounded-xl border bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">NIP</th>
                        <th class="px-4 py-3">Kapasitas Bimbingan</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($dosens as $dosen)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $dosen->nama }}</td>
                            <td class="px-4 py-3 font-mono text-gray-600">{{ $dosen->nip }}</td>
                            <td class="px-4 py-3 text-gray-500">
                                @if ($dosen->kapasitas_maksimal)
                                    {{ $dosen->kapasitas_maksimal }} mhs
                                @else
                                    <span class="text-gray-400 italic">Tidak dibatasi</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.dosen.edit', $dosen) }}"
                                   class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-medium text-brand-600 hover:bg-brand-50 transition-colors">
                                    <x-icon name="pencil" class="h-3.5 w-3.5" />
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-400">
                                Belum ada data dosen.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($dosens->hasPages())
            <div>{{ $dosens->links() }}</div>
        @endif
    </div>
</x-app-layout>

