<x-app-layout>
    <x-slot name="title">Tambah Mahasiswa</x-slot>

    <div class="mx-auto max-w-xl">
        <div class="mb-4 flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('admin.mahasiswa.index') }}" class="hover:text-brand-600">Data Mahasiswa</a>
            <span>/</span>
            <span class="text-gray-700">Tambah</span>
        </div>

        <div class="rounded-xl border bg-white p-6 shadow-sm">
            <h2 class="mb-5 text-base font-semibold text-gray-800">Form Tambah Mahasiswa</h2>

            <form method="POST" action="{{ route('admin.mahasiswa.store') }}" class="space-y-4">
                @csrf

                {{-- NIM --}}
                <div>
                    <x-input-label for="nim" value="NIM *" />
                    <x-text-input id="nim" name="nim" type="text" class="mt-1 block w-full"
                                  :value="old('nim')" placeholder="Contoh: 2021001" autofocus />
                    <x-input-error :messages="$errors->get('nim')" class="mt-1" />
                </div>

                {{-- Nama --}}
                <div>
                    <x-input-label for="name" value="Nama Lengkap *" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                  :value="old('name')" placeholder="Nama sesuai KTP" />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>

                {{-- Email --}}
                <div>
                    <x-input-label for="email" value="Email *" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                                  :value="old('email')" placeholder="email@example.com" />
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>

                {{-- Angkatan --}}
                <div>
                    <x-input-label for="angkatan" value="Angkatan *" />
                    <x-text-input id="angkatan" name="angkatan" type="number" class="mt-1 block w-full"
                                  :value="old('angkatan')" placeholder="Contoh: 2021" min="2000" max="{{ date('Y') + 1 }}" />
                    <x-input-error :messages="$errors->get('angkatan')" class="mt-1" />
                </div>

                {{-- Alamat --}}
                <div>
                    <x-input-label for="alamat" value="Alamat Lengkap" />
                    <textarea id="alamat" name="alamat" rows="3"
                              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                              placeholder="Alamat lengkap mahasiswa (opsional)">{{ old('alamat') }}</textarea>
                    <x-input-error :messages="$errors->get('alamat')" class="mt-1" />
                </div>

                {{-- Password --}}
                <div>
                    <x-input-label for="password" value="Password (kosongkan = gunakan NIM)" />
                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full"
                                  placeholder="Minimal 6 karakter" autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                    <p class="mt-1 text-xs text-gray-400">Jika dikosongkan, password default adalah NIM mahasiswa.</p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('admin.mahasiswa.index') }}"
                       class="rounded-lg border px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Batal</a>
                    <x-primary-button>Simpan</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
