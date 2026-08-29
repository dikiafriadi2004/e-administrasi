@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-xl border-slate-200 bg-white shadow-sm placeholder:text-slate-400 focus:border-brand-400 focus:ring-brand-400 disabled:bg-slate-50 disabled:text-slate-500 transition-colors text-sm']) }}>
