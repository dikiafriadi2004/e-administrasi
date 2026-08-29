<x-app-layout>
    <x-slot name="title">Edit Mahasiswa</x-slot>

    <div class="mx-auto max-w-xl">
        <div class="mb-4 flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('admin.mahasiswa.index') }}" class="hover:text-brand-600">Data Mahasiswa</a>
            <span>/</span>
            <span class="text-gray-700">Edit</span>
        </div>

        <div class="rounded-xl border bg-white p-6 shadow-sm">
            <h2 class="mb-1 text-base font-semibold text-gray-800">Edit Data Mahasiswa</h2>
            <p class="mb-5 text-xs text-gray-400">NIM tidak dapat diubah: <strong class="font-mono">{{ $mahasiswa->nim }}</strong></p>

            <form method="POST" action="{{ route('admin.mahasiswa.update', $mahasiswa) }}" class="space-y-4">
                @csrf
                @method('PUT')

                {{-- Nama --}}
                <div>
                    <x-input-label for="name" value="Nama Lengkap *" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                  :value="old('name', $mahasiswa->user->name)" autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>

                {{-- Email --}}
                <div>
                    <x-input-label for="email" value="Email *" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                                  :value="old('email', $mahasiswa->user->email)" />
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>

                {{-- Angkatan --}}
                <div>
                    <x-input-label for="angkatan" value="Angkatan *" />
                    <x-text-input id="angkatan" name="angkatan" type="number" class="mt-1 block w-full"
                                  :value="old('angkatan', $mahasiswa->angkatan)" min="2000" max="{{ date('Y') + 1 }}" />
                    <x-input-error :messages="$errors->get('angkatan')" class="mt-1" />
                </div>

                {{-- Alamat --}}
                <div>
                    <x-input-label for="alamat" value="Alamat Lengkap" />
                    <textarea id="alamat" name="alamat" rows="3"
                              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                              placeholder="Alamat lengkap mahasiswa (opsional)">{{ old('alamat', $mahasiswa->alamat) }}</textarea>
                    <x-input-error :messages="$errors->get('alamat')" class="mt-1" />
                </div>

                {{-- Reset Password (opsional) --}}
                <div>
                    <x-input-label for="password" value="Reset Password (opsional)" />
                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full"
                                  placeholder="Kosongkan jika tidak ingin mengubah" autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('admin.mahasiswa.index') }}"
                       class="rounded-lg border px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Batal</a>
                    <x-primary-button>Simpan Perubahan</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
