@props(['label', 'value', 'icon' => null, 'color' => 'brand'])

@php
$colorMap = [
    'brand'  => ['bg' => 'bg-brand-50',   'icon' => 'text-brand-600',   'ring' => 'ring-brand-100'],
    'indigo' => ['bg' => 'bg-indigo-50',  'icon' => 'text-indigo-600',  'ring' => 'ring-indigo-100'],
    'blue'   => ['bg' => 'bg-sky-50',     'icon' => 'text-sky-600',     'ring' => 'ring-sky-100'],
    'green'  => ['bg' => 'bg-emerald-50', 'icon' => 'text-emerald-600', 'ring' => 'ring-emerald-100'],
    'yellow' => ['bg' => 'bg-amber-50',   'icon' => 'text-amber-600',   'ring' => 'ring-amber-100'],
    'red'    => ['bg' => 'bg-red-50',     'icon' => 'text-red-500',     'ring' => 'ring-red-100'],
];
$c = $colorMap[$color] ?? $colorMap['brand'];
@endphp

<div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
    <div class="flex items-start justify-between gap-3">
        <div class="flex-1 min-w-0">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $label }}</p>
            <p class="mt-2 text-3xl font-bold tracking-tight text-slate-800">{{ number_format($value) }}</p>
        </div>
        @if ($icon)
            <div @class(['flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ring-1', $c['bg'], $c['ring']])>
                <x-icon :name="$icon" @class(['h-5 w-5', $c['icon']]) />
            </div>
        @endif
    </div>
</div>
