@props(['href', 'active' => false, 'icon' => null])

<a href="{{ $href }}"
   @class([
       'flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition-colors',
       'bg-brand-50 text-brand-700 font-semibold' => $active,
       'text-slate-600 hover:bg-slate-50 hover:text-slate-900' => ! $active,
   ])>
    @if ($icon)
        <x-icon :name="$icon" @class(['h-4 w-4 shrink-0', 'text-brand-600' => $active, 'text-slate-400' => ! $active]) />
    @endif
    {{ $slot }}
    @if ($active)
        <span class="ml-auto h-1.5 w-1.5 rounded-full bg-brand-500"></span>
    @endif
</a>
