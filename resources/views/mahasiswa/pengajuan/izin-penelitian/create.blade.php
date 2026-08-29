<x-app-layout>
    <x-slot name="title">Ajukan Surat Izin Penelitian</x-slot>

    <div class="mb-4 flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('mahasiswa.riwayat.index') }}" class="hover:text-brand-600">Riwayat</a>
        <span>/</span>
        <span class="text-gray-700">Surat Izin Penelitian</span>
    </div>

    <livewire:mahasiswa.pengajuan-izin-penelitian-form />
</x-app-layout>
