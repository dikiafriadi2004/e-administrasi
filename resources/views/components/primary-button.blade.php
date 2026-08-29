<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:ring-offset-2 active:bg-brand-700 transition-colors disabled:opacity-60 disabled:cursor-not-allowed']) }}>
    {{ $slot }}
</button>
