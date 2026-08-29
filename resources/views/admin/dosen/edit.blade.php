<x-app-layout>
    <x-slot name="title">Edit Dosen</x-slot>

    <div class="mx-auto max-w-xl">
        <div class="mb-4 flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('admin.dosen.index') }}" class="hover:text-brand-600">Data Dosen</a>
            <span>/</span>
            <span class="text-gray-700">Edit</span>
        </div>

        <div class="rounded-xl border bg-white p-6 shadow-sm">
            <h2 class="mb-5 text-base font-semibold text-gray-800">Edit Data Dosen</h2>

            <form method="POST" action="{{ route('admin.dosen.update', $dosen) }}" class="space-y-4">
                @csrf
                @method('PUT')

                {{-- Nama --}}
                <div>
                    <x-input-label for="nama" value="Nama Lengkap & Gelar *" />
                    <x-text-input id="nama" name="nama" type="text" class="mt-1 block w-full"
                                  :value="old('nama', $dosen->nama)" autofocus />
                    <x-input-error :messages="$errors->get('nama')" class="mt-1" />
                </div>

                {{-- NIP --}}
                <div>
                    <x-input-label for="nip" value="NIP *" />
                    <x-text-input id="nip" name="nip" type="text" class="mt-1 block w-full"
                                  :value="old('nip', $dosen->nip)" />
                    <x-input-error :messages="$errors->get('nip')" class="mt-1" />
                </div>

                {{-- Kapasitas --}}
                <div>
                    <x-input-label for="kapasitas_maksimal" value="Kapasitas Maksimal Bimbingan (opsional)" />
                    <x-text-input id="kapasitas_maksimal" name="kapasitas_maksimal" type="number"
                                  class="mt-1 block w-full"
                                  :value="old('kapasitas_maksimal', $dosen->kapasitas_maksimal)"
                                  placeholder="Kosongkan jika tidak dibatasi" min="1" max="99" />
                    <x-input-error :messages="$errors->get('kapasitas_maksimal')" class="mt-1" />
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('admin.dosen.index') }}"
                       class="rounded-lg border px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Batal</a>
                    <x-primary-button>Simpan Perubahan</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
