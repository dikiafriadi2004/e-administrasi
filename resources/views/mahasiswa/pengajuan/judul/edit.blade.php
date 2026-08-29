<x-app-layout>
    <x-slot name="title">Revisi Judul Skripsi</x-slot>

    <div class="mb-4 flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('mahasiswa.riwayat.index') }}" class="hover:text-brand-600 transition-colors">Riwayat</a>
        <x-icon name="chevron-right" class="h-4 w-4 text-slate-300" />
        <span class="text-slate-700 font-medium">Revisi Judul</span>
    </div>

    <livewire:mahasiswa.edit-judul-form :pengajuan="$pengajuan" />
</x-app-layout>
