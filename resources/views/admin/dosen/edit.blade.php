<x-app-layout>
    <x-slot name="title">Edit Dosen</x-slot>

    <div class="mx-auto max-w-2xl">
        <div class="mb-4 flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('admin.dosen.index') }}" class="hover:text-brand-600">Data Dosen</a>
            <span>/</span>
            <span class="text-gray-700">Edit</span>
        </div>

        <div class="rounded-xl border bg-white p-6 shadow-sm">
            <h2 class="mb-5 text-base font-semibold text-gray-800">Edit Data Dosen</h2>

            <form method="POST" action="{{ route('admin.dosen.update', $dosen) }}" class="space-y-5">
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
                    <p class="mt-1 text-xs text-gray-400">Kosongkan jika tidak dibatasi.</p>
                    <x-input-error :messages="$errors->get('kapasitas_maksimal')" class="mt-1" />
                </div>

                {{-- Bidang Kajian --}}
                @php $bidangInit = old('bidang_kajian', $dosen->bidang_kajian ?? []); @endphp
                <div x-data="tagInput('bidang_kajian', @json($bidangInit))">
                    <x-input-label value="Bidang Kajian (opsional, bisa banyak)" />
                    <p class="mb-2 text-xs text-slate-400">Ketik bidang kajian lalu tekan Enter atau klik Tambah.</p>

                    <div class="mb-2 flex flex-wrap gap-1.5 min-h-8">
                        <template x-for="(item, i) in items" :key="i">
                            <span class="inline-flex items-center gap-1 rounded-full bg-brand-100 px-3 py-1 text-xs font-medium text-brand-700">
                                <span x-text="item"></span>
                                <button type="button" @click="remove(i)" class="text-brand-400 hover:text-brand-700">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                                <input type="hidden" :name="'bidang_kajian['+i+']'" :value="item">
                            </span>
                        </template>
                    </div>

                    <div class="flex gap-2">
                        <input type="text" x-model="input"
                               @keydown.enter.prevent="add()"
                               @keydown.comma.prevent="add()"
                               placeholder="Contoh: Rekayasa Perangkat Lunak"
                               class="flex-1 rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400" />
                        <button type="button" @click="add()"
                                class="rounded-xl bg-brand-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-600">
                            + Tambah
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('bidang_kajian')" class="mt-1" />
                </div>

                {{-- Mata Kuliah --}}
                @php $mkInit = old('mata_kuliah', $dosen->mata_kuliah ?? []); @endphp
                <div x-data="tagInput('mata_kuliah', @json($mkInit))">
                    <x-input-label value="Mata Kuliah yang Diampu (opsional, bisa banyak)" />
                    <p class="mb-2 text-xs text-slate-400">Ketik nama mata kuliah lalu tekan Enter atau klik Tambah.</p>

                    <div class="mb-2 flex flex-wrap gap-1.5 min-h-8">
                        <template x-for="(item, i) in items" :key="i">
                            <span class="inline-flex items-center gap-1 rounded-full bg-sky-100 px-3 py-1 text-xs font-medium text-sky-700">
                                <span x-text="item"></span>
                                <button type="button" @click="remove(i)" class="text-sky-400 hover:text-sky-700">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                                <input type="hidden" :name="'mata_kuliah['+i+']'" :value="item">
                            </span>
                        </template>
                    </div>

                    <div class="flex gap-2">
                        <input type="text" x-model="input"
                               @keydown.enter.prevent="add()"
                               @keydown.comma.prevent="add()"
                               placeholder="Contoh: Pemrograman Web"
                               class="flex-1 rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400" />
                        <button type="button" @click="add()"
                                class="rounded-xl bg-sky-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-sky-600">
                            + Tambah
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('mata_kuliah')" class="mt-1" />
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('admin.dosen.index') }}"
                       class="rounded-lg border px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Batal</a>
                    <x-primary-button>Simpan Perubahan</x-primary-button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function tagInput(field, initial) {
        return {
            input: '',
            items: initial || [],
            add() {
                const v = this.input.trim().replace(/,+$/, '');
                if (v && !this.items.includes(v)) {
                    this.items.push(v);
                }
                this.input = '';
            },
            remove(i) {
                this.items.splice(i, 1);
            }
        }
    }
    </script>
    @endpush
</x-app-layout>
