<x-app-layout>
    <x-slot name="title">Pengaturan Sistem</x-slot>

    <div class="mx-auto max-w-2xl space-y-6">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Pengaturan Sistem</h2>
            <p class="mt-1 text-sm text-slate-500">
                Konfigurasi identitas institusi dan sistem. Perubahan langsung tersimpan ke database.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.pengaturan.update') }}" class="space-y-5">
            @csrf
            @method('PUT')

            @php
                $grupConfig = [
                    'institusi' => ['label' => 'Identitas Institusi',     'icon' => 'landmark'],
                    'kaprodi'   => ['label' => 'Kepala Program Studi & Dekan', 'icon' => 'user-round-check'],
                    'akademik'  => ['label' => 'Kalender Akademik',        'icon' => 'calendar-days'],
                    'penomoran' => ['label' => 'Penomoran Surat',          'icon' => 'hash'],                    'sistem'    => ['label' => 'Konfigurasi Sistem',       'icon' => 'settings'],
                ];
            @endphp

            @foreach ($pengaturan as $grup => $items)
                @php $cfg = $grupConfig[$grup] ?? ['label' => ucfirst($grup), 'icon' => 'settings']; @endphp
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="mb-5 flex items-center gap-2 text-sm font-semibold text-slate-700">
                        <x-icon :name="$cfg['icon']" class="h-4 w-4 text-brand-500" />
                        {{ $cfg['label'] }}
                    </h3>
                    <div class="space-y-4">
                        @foreach ($items as $item)
                            <div>
                                <x-input-label :for="$item->key" :value="$item->label" />

                                @if ($item->key === 'semester_aktif')
                                    <select id="{{ $item->key }}" name="{{ $item->key }}"
                                            class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-brand-400 focus:ring-brand-400 text-sm">
                                        <option value="Ganjil" @selected(old($item->key, $item->value) === 'Ganjil')>Ganjil</option>
                                        <option value="Genap"  @selected(old($item->key, $item->value) === 'Genap')>Genap</option>
                                    </select>
                                    <p class="mt-1 text-xs text-slate-400">Update setiap awal semester baru sesuai kalender akademik kampus.</p>
                                @elseif ($item->key === 'tahun_akademik')
                                    <x-text-input :id="$item->key" :name="$item->key"
                                                  type="text" class="mt-1 block w-full"
                                                  :value="old($item->key, $item->value)"
                                                  placeholder="2025/2026" />
                                    <p class="mt-1 text-xs text-slate-400">Format: TTTT/TTTT, contoh <code class="rounded bg-slate-100 px-1 font-mono">2025/2026</code></p>
                                @elseif ($item->key === 'alamat_prodi')
                                    <textarea id="{{ $item->key }}" name="{{ $item->key }}"
                                              rows="2"
                                              class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-brand-400 focus:ring-brand-400 text-sm">{{ old($item->key, $item->value) }}</textarea>
                                @elseif ($item->key === 'libreoffice_path')
                                    <x-text-input :id="$item->key" :name="$item->key"
                                                  type="text" class="mt-1 block w-full"
                                                  :value="old($item->key, $item->value)"
                                                  placeholder="Kosongkan = auto-detect (soffice di PATH)" />
                                    <p class="mt-1 text-xs text-slate-400">
                                        Linux VPS: kosongkan saja.
                                        Windows: <code class="rounded bg-slate-100 px-1 font-mono text-xs">C:/Program Files/LibreOffice/program/soffice.exe</code>
                                    </p>
                                @else
                                    <x-text-input :id="$item->key" :name="$item->key"
                                                  type="text" class="mt-1 block w-full"
                                                  :value="old($item->key, $item->value)" />
                                @endif
                                <x-input-error :messages="$errors->get($item->key)" class="mt-1" />
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="flex justify-end">
                <x-primary-button>
                    <x-icon name="save" class="h-4 w-4" />
                    Simpan Semua Pengaturan
                </x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
