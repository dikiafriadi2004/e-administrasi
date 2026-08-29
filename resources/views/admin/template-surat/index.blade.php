<x-app-layout>
    <x-slot name="title">Template Surat</x-slot>

    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Manajemen Template Surat</h2>
                <p class="text-sm text-gray-500">Template .docx aktif yang digunakan untuk generate surat.</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-5 py-3">Jenis Surat</th>
                        <th class="px-5 py-3">File</th>
                        <th class="px-5 py-3">Ukuran</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($jenisTersedia as $kode => $label)
                        @php $tpl = $templates->get($kode); @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 font-medium text-gray-800">{{ $label }}</td>
                            <td class="px-5 py-3 font-mono text-xs text-gray-500">
                                {{ $tpl ? basename($tpl->path_file) : '—' }}
                            </td>
                            <td class="px-5 py-3 text-gray-500 text-xs">
                                @if ($tpl && \Illuminate\Support\Facades\Storage::disk('private')->exists($tpl->path_file))
                                    @php
                                        $bytes = \Illuminate\Support\Facades\Storage::disk('private')->size($tpl->path_file);
                                        $kb = round($bytes / 1024, 1);
                                    @endphp
                                    {{ $kb }} KB
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                @if ($tpl)
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">
                                        Belum Ada
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex flex-wrap gap-2">
                                    @if ($tpl)
                                        <a href="{{ route('admin.template-surat.download', $kode) }}"
                                           class="inline-flex items-center gap-1.5 rounded-lg border border-brand-200 bg-brand-50 px-2.5 py-1 text-xs font-medium text-brand-700 hover:bg-brand-100 transition-colors">
                                            <x-icon name="download" class="h-3.5 w-3.5" />
                                            Download
                                        </a>
                                    @endif

                                    {{-- Upload versi baru --}}
                                    <a href="{{ route('admin.template-surat.upload', $kode) }}"
                                       class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                                        <x-icon :name="$tpl ? 'upload' : 'plus'" class="h-3.5 w-3.5" />
                                        {{ $tpl ? 'Upload Versi Baru' : 'Upload Template' }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="rounded-2xl border border-brand-100 bg-brand-50 p-5 space-y-3">
            <p class="flex items-center gap-2 text-sm font-semibold text-brand-800">
                <x-icon name="info" class="h-4 w-4 text-brand-600" />
                Cara mengedit template:
            </p>
            <ol class="list-decimal list-inside space-y-1.5 text-sm text-brand-700">
                <li>Klik <strong>Download</strong> untuk mengunduh template aktif saat ini</li>
                <li>Edit file <code>.docx</code> menggunakan Microsoft Word — ubah teks, layout, atau kop surat sesuai kebutuhan</li>
                <li>Jangan ubah placeholder <code>${'{'}nama_placeholder{'}'}</code> — hanya isi teks biasa yang boleh diubah</li>
                <li>Klik <strong>Upload Versi Baru</strong> — file lama <strong>otomatis dihapus</strong> dari server, diganti dengan yang baru</li>
            </ol>
        </div>
    </div>
</x-app-layout>
