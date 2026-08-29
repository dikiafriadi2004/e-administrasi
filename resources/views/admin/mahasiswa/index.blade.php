<x-app-layout>
    <x-slot name="title">Data Mahasiswa</x-slot>

    <div class="space-y-4"
         x-data="{ modalToggle: false, toggleName: '', toggleAction: '', toggleLabel: '', toggleIsActive: false }">

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Data Mahasiswa</h2>
                <p class="text-sm text-slate-500">Total: {{ $mahasiswas->total() }} mahasiswa terdaftar</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.mahasiswa.import.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 shadow-sm hover:bg-slate-50 transition-colors">
                    <x-icon name="upload" class="h-4 w-4" />
                    Import Excel
                </a>
                <a href="{{ route('admin.mahasiswa.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 transition-colors">
                    <x-icon name="user-plus" class="h-4 w-4" />
                    Tambah Mahasiswa
                </a>
            </div>
        </div>

        {{-- Tabel --}}

        <div class="mb-2 flex items-center justify-between gap-3">
            <x-per-page-selector :current="$perPage ?? 10" />
            <p class="text-xs text-slate-400">Total: {{ $mahasiswas->total() }} data</p>
        </div>
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3">NIM</th>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Angkatan</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($mahasiswas as $mhs)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 font-mono font-medium text-slate-700">{{ $mhs->nim }}</td>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $mhs->user->name }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $mhs->user->email }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $mhs->angkatan }}</td>
                            <td class="px-4 py-3">
                                @if ($mhs->user->is_active)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 shrink-0"></span>Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-400 shrink-0"></span>Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.mahasiswa.edit', $mhs) }}"
                                       class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-medium text-brand-600 hover:bg-brand-50 transition-colors">
                                        <x-icon name="pencil" class="h-3.5 w-3.5" />
                                        Edit
                                    </a>
                                    <button type="button"
                                            @click="
                                                modalToggle = true;
                                                toggleName = '{{ addslashes($mhs->user->name) }}';
                                                toggleAction = '{{ route('admin.mahasiswa.toggle-active', $mhs) }}';
                                                toggleLabel = '{{ $mhs->user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}';
                                                toggleIsActive = {{ $mhs->user->is_active ? 'true' : 'false' }};
                                            "
                                            class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-medium transition-colors {{ $mhs->user->is_active ? 'text-red-600 hover:bg-red-50' : 'text-emerald-600 hover:bg-emerald-50' }}">
                                        @if ($mhs->user->is_active)
                                            <x-icon name="user-x" class="h-3.5 w-3.5" />
                                            Nonaktifkan
                                        @else
                                            <x-icon name="user-check" class="h-3.5 w-3.5" />
                                            Aktifkan
                                        @endif
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center">
                                <div class="flex flex-col items-center gap-2 text-slate-400">
                                    <x-icon name="users" class="h-8 w-8" />
                                    <p class="text-sm">Belum ada data mahasiswa.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($mahasiswas->hasPages())
            <div>{{ $mahasiswas->links() }}</div>
        @endif

        {{-- Modal Konfirmasi Toggle Aktif --}}
        <div x-show="modalToggle" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div @click.stop
                 class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5"
                 x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

                {{-- Icon --}}
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl"
                     :class="toggleIsActive ? 'bg-red-100' : 'bg-emerald-100'">
                    <template x-if="toggleIsActive">
                        <x-icon name="user-x" class="h-6 w-6 text-red-600" />
                    </template>
                    <template x-if="!toggleIsActive">
                        <x-icon name="user-check" class="h-6 w-6 text-emerald-600" />
                    </template>
                </div>

                <h3 class="text-base font-bold text-slate-900" x-text="toggleLabel + ' Akun'"></h3>
                <p class="mt-1.5 text-sm text-slate-500">
                    Apakah Anda yakin ingin <span x-text="toggleLabel.toLowerCase()"></span> akun
                    <strong x-text="toggleName" class="text-slate-800"></strong>?
                </p>

                <div class="mt-5 flex justify-end gap-3">
                    <button @click="modalToggle = false"
                            class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <form :action="toggleAction" method="POST">
                        @csrf
                        <button type="submit"
                                class="rounded-xl px-4 py-2 text-sm font-semibold text-white transition-colors"
                                :class="toggleIsActive ? 'bg-red-600 hover:bg-red-700' : 'bg-emerald-600 hover:bg-emerald-700'"
                                x-text="'Ya, ' + toggleLabel">
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
