<x-app-layout>
    <x-slot name="title">Import Mahasiswa dari Excel</x-slot>

    <div class="mx-auto max-w-2xl space-y-5">
        <div class="mb-4 flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('admin.mahasiswa.index') }}" class="hover:text-brand-600">Data Mahasiswa</a>
            <span>/</span>
            <span class="text-gray-700">Import Excel</span>
        </div>

        {{-- Form Upload --}}
        <div class="rounded-xl border bg-white p-6 shadow-sm">
            <h2 class="mb-1 text-base font-semibold text-gray-800">Import Massal Mahasiswa</h2>
            <p class="mb-5 text-sm text-gray-500">
                Upload file Excel dengan kolom: <code class="rounded bg-gray-100 px-1 font-mono text-xs">nim</code>,
                <code class="rounded bg-gray-100 px-1 font-mono text-xs">nama</code>,
                <code class="rounded bg-gray-100 px-1 font-mono text-xs">email</code>,
                <code class="rounded bg-gray-100 px-1 font-mono text-xs">angkatan</code>.
                Duplikat NIM/email akan dilewati.
            </p>

            <form method="POST" action="{{ route('admin.mahasiswa.import.store') }}"
                  enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <x-input-label for="file_excel" value="File Excel (.xlsx / .xls / .csv) *" />
                    <input id="file_excel" name="file_excel" type="file"
                           accept=".xlsx,.xls,.csv"
                           class="mt-1 block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm
                                  file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1
                                  file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100" />
                    <x-input-error :messages="$errors->get('file_excel')" class="mt-1" />
                    <p class="mt-1 text-xs text-slate-400">Maks 5 MB · Password default setiap mahasiswa = NIM-nya</p>
                </div>
                <x-primary-button>Upload & Proses</x-primary-button>
            </form>
        </div>

        {{-- Panduan Format --}}
        <div class="rounded-2xl border border-brand-100 bg-brand-50 p-4 text-sm text-brand-800">
            <p class="mb-2 flex items-center gap-2 font-semibold">
                <x-icon name="table" class="h-4 w-4 text-brand-600" />
                Format kolom Excel (baris pertama = header):
            </p>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-blue-200 text-xs">
                    <thead>
                        <tr class="text-left font-semibold text-brand-700">
                            <th class="px-3 py-1.5">nim</th>
                            <th class="px-3 py-1.5">nama</th>
                            <th class="px-3 py-1.5">email</th>
                            <th class="px-3 py-1.5">angkatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-100">
                        <tr class="text-brand-700">
                            <td class="px-3 py-1.5 font-mono">2021001</td>
                            <td class="px-3 py-1.5">Budi Santoso</td>
                            <td class="px-3 py-1.5">budi@mail.com</td>
                            <td class="px-3 py-1.5">2021</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Ringkasan Hasil Import --}}
        @if (session('import_result'))
            @php
                $result    = session('import_result');
                $berhasil  = $result['berhasil'] ?? [];
                $dilewati  = $result['dilewati'] ?? [];
                $gagal     = $result['gagal'] ?? [];
                $total     = count($berhasil) + count($dilewati) + count($gagal);
            @endphp
            <div class="rounded-xl border bg-white p-6 shadow-sm space-y-4">
                <h3 class="text-sm font-semibold text-gray-800">Hasil Import ({{ $total }} baris diproses)</h3>

                {{-- Ringkasan angka --}}
                <div class="grid grid-cols-3 gap-3">
                    <div class="rounded-lg bg-green-50 border border-green-200 p-3 text-center">
                        <p class="text-2xl font-bold text-green-700">{{ count($berhasil) }}</p>
                        <p class="text-xs text-green-600 mt-0.5">Berhasil Dibuat</p>
                    </div>
                    <div class="rounded-lg bg-yellow-50 border border-yellow-200 p-3 text-center">
                        <p class="text-2xl font-bold text-yellow-700">{{ count($dilewati) }}</p>
                        <p class="text-xs text-yellow-600 mt-0.5">Dilewati (Duplikat)</p>
                    </div>
                    <div class="rounded-lg bg-red-50 border border-red-200 p-3 text-center">
                        <p class="text-2xl font-bold text-red-700">{{ count($gagal) }}</p>
                        <p class="text-xs text-red-600 mt-0.5">Gagal (Data Tidak Valid)</p>
                    </div>
                </div>

                {{-- Detail dilewati --}}
                @if (count($dilewati) > 0)
                    <div>
                        <p class="mb-2 text-xs font-semibold text-yellow-700">Dilewati (duplikat):</p>
                        <ul class="space-y-1">
                            @foreach ($dilewati as $item)
                                <li class="rounded bg-yellow-50 px-3 py-1.5 text-xs text-yellow-800">
                                    Baris {{ $item['baris'] }} â€” NIM <strong>{{ $item['nim'] }}</strong>: {{ $item['alasan'] }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Detail gagal --}}
                @if (count($gagal) > 0)
                    <div>
                        <p class="mb-2 text-xs font-semibold text-red-700">Gagal (data tidak valid):</p>
                        <ul class="space-y-1">
                            @foreach ($gagal as $item)
                                <li class="rounded bg-red-50 px-3 py-1.5 text-xs text-red-800">
                                    Baris {{ $item['baris'] }} â€” NIM <strong>{{ $item['nim'] }}</strong>: {{ $item['alasan'] }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (count($berhasil) > 0)
                    <div class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        <x-icon name="circle-check" class="h-4 w-4 shrink-0 text-emerald-600" />
                        {{ count($berhasil) }} akun mahasiswa berhasil dibuat. Password default = NIM masing-masing.
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-app-layout>

