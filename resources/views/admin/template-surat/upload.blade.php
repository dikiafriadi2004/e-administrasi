<x-app-layout>
    <x-slot name="title">Upload Template — {{ $labelJenis }}</x-slot>

    <div class="mx-auto max-w-2xl space-y-5">
        <div class="mb-4 flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('admin.template-surat.index') }}" class="hover:text-brand-600">Template Surat</a>
            <span>/</span>
            <span class="text-gray-700">Upload — {{ $labelJenis }}</span>
        </div>

        {{-- Form Upload --}}
        <div class="rounded-xl border bg-white p-6 shadow-sm">
            <h2 class="mb-1 text-base font-semibold text-gray-800">Upload Template: {{ $labelJenis }}</h2>
            <p class="mb-5 text-xs text-gray-400">
                Format: <code>.docx</code> · Maks 5 MB ·
                <strong class="text-red-600">File lama akan dihapus otomatis</strong> saat upload berhasil.
            </p>

            <form method="POST"
                  action="{{ route('admin.template-surat.store', $jenis) }}"
                  enctype="multipart/form-data"
                  class="space-y-5">
                @csrf

                <div>
                    <x-input-label for="template_file" value="File Template (.docx) *" />
                    <input id="template_file" name="template_file" type="file"
                           accept=".docx"
                           class="mt-1 block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700
                                  file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1
                                  file:text-sm file:font-medium file:text-brand-700
                                  hover:file:bg-brand-100 focus:outline-none" />
                    <x-input-error :messages="$errors->get('template_file')" class="mt-1" />
                </div>

                <div class="flex items-center justify-between pt-1">
                    <a href="{{ route('admin.template-surat.index') }}"
                       class="rounded-lg border px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">
                        Batal
                    </a>
                    <x-primary-button>Upload Template</x-primary-button>
                </div>
            </form>
        </div>

        {{-- Tabel Placeholder Wajib --}}
        <div class="rounded-xl border bg-white p-6 shadow-sm">
            <h3 class="mb-1 text-sm font-semibold text-gray-800">Placeholder Wajib — {{ $labelJenis }}</h3>
            <p class="mb-4 text-xs text-gray-500">
                Semua placeholder di bawah harus ada di file template .docx Anda (ditulis persis seperti ini, termasuk <code>${'{'}</code> dan <code>{'}'}</code>).
                Placeholder yang hilang akan dikosongkan saat generate.
            </p>

            <div class="overflow-hidden rounded-2xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-4 py-2 text-left">Placeholder</th>
                            <th class="px-4 py-2 text-left">Keterangan</th>
                            <th class="px-4 py-2 text-left">Sumber Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($placeholders as $placeholder => $info)
                            @php
                                $keterangan = is_array($info) ? $info['desc'] : $info;
                                $sumber     = is_array($info) ? $info['sumber'] : 'Data Pengajuan';
                                $badgeClass = match(true) {
                                    str_contains($sumber, 'Pengaturan')  => 'bg-blue-100 text-blue-700',
                                    str_contains($sumber, 'Otomatis')    => 'bg-green-100 text-green-700',
                                    str_contains($sumber, 'admin')       => 'bg-brand-100 text-brand-700',
                                    str_contains($sumber, 'Kaprodi')     => 'bg-purple-100 text-purple-700',
                                    str_contains($sumber, 'mahasiswa')   => 'bg-amber-100 text-amber-700',
                                    default                              => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-2.5">
                                    <code class="rounded bg-yellow-50 border border-yellow-200 px-2 py-0.5 text-xs font-mono text-yellow-800">
                                        {{ $placeholder }}
                                    </code>
                                </td>
                                <td class="px-4 py-2.5 text-gray-700">{{ $keterangan }}</td>
                                <td class="px-4 py-2.5">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $badgeClass }}">
                                        {{ $sumber }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 rounded-2xl border border-brand-200 bg-brand-50 px-4 py-3 text-xs text-brand-800">
                <p class="flex items-center gap-2 font-semibold mb-2">
                    <svg class="h-4 w-4 text-brand-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                    Tips penulisan di Word:
                </p>
                <ul class="list-disc list-inside space-y-1">
                    <li>Ketik placeholder <strong>langsung di body dokumen</strong> — jangan di text box atau shape</li>
                    <li>Gunakan format: <code class="rounded bg-brand-100 px-1 font-mono">${'{'}nama_placeholder{'}'}</code> tanpa spasi di dalam kurung</li>
                    <li>Jika placeholder terpotong oleh format Word, matikan autocorrect atau ketik ulang dalam satu kesatuan</li>
                </ul>
            </div>
        </div>

    </div>
</x-app-layout>
