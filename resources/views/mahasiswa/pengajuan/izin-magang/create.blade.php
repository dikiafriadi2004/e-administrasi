<x-app-layout>
    <x-slot name="title">Ajukan Surat Izin Magang / PKL</x-slot>

    <div class="mb-4 flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('mahasiswa.riwayat.index') }}" class="hover:text-brand-600">Riwayat</a>
        <span>/</span>
        <span class="text-gray-700">Surat Izin Magang</span>
    </div>

    <livewire:mahasiswa.pengajuan-izin-magang-form />
</x-app-layout>
