<x-app-layout>
    <x-slot name="title">Ajukan Sidang Skripsi</x-slot>

    <div class="mb-4 flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('mahasiswa.riwayat.index') }}" class="hover:text-brand-600">Riwayat</a>
        <span>/</span>
        <span class="text-gray-700">Sidang Skripsi</span>
    </div>

    <livewire:mahasiswa.pengajuan-sidang-form :pengajuan-judul="$pengajuanJudul" />
</x-app-layout>

