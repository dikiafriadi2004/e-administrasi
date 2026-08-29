@props(['status'])

@php
$map = [
    'diajukan'             => ['label' => 'Diajukan',             'dot' => 'bg-amber-400',   'bg' => 'bg-amber-50',   'text' => 'text-amber-700',  'ring' => 'ring-amber-200'],
    'diverifikasi'         => ['label' => 'Diverifikasi',         'dot' => 'bg-blue-400',    'bg' => 'bg-blue-50',    'text' => 'text-blue-700',   'ring' => 'ring-blue-200'],
    'menunggu_ttd'         => ['label' => 'Menunggu TTD',         'dot' => 'bg-violet-400',  'bg' => 'bg-violet-50',  'text' => 'text-violet-700', 'ring' => 'ring-violet-200'],
    'sudah_ditandatangani' => ['label' => 'Sudah TTD',            'dot' => 'bg-teal-400',    'bg' => 'bg-teal-50',    'text' => 'text-teal-700',   'ring' => 'ring-teal-200'],
    'selesai'              => ['label' => 'Selesai',              'dot' => 'bg-emerald-400', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700','ring' => 'ring-emerald-200'],
    'ditolak'              => ['label' => 'Ditolak',              'dot' => 'bg-red-400',     'bg' => 'bg-red-50',     'text' => 'text-red-700',    'ring' => 'ring-red-200'],
    'disetujui'            => ['label' => 'Disetujui',            'dot' => 'bg-emerald-400', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700','ring' => 'ring-emerald-200'],
];
$item = $map[$status] ?? ['label' => ucfirst(str_replace('_', ' ', $status)), 'dot' => 'bg-slate-400', 'bg' => 'bg-slate-50', 'text' => 'text-slate-600', 'ring' => 'ring-slate-200'];
@endphp

<span @class(['inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset', $item['bg'], $item['text'], $item['ring']])>
    <span @class(['h-1.5 w-1.5 rounded-full shrink-0', $item['dot']])></span>
    {{ $item['label'] }}
</span>
