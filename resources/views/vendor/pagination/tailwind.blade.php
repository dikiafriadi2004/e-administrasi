@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between py-3">
        {{-- Mobile: prev/next saja --}}
        <div class="flex flex-1 justify-between sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="inline-flex cursor-default items-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-300">
                    ← Sebelumnya
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}"
                   class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                    ← Sebelumnya
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}"
                   class="ml-3 inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                    Berikutnya →
                </a>
            @else
                <span class="ml-3 inline-flex cursor-default items-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-300">
                    Berikutnya →
                </span>
            @endif
        </div>

        {{-- Desktop: info + tombol halaman --}}
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <p class="text-xs text-slate-500">
                Menampilkan
                <span class="font-semibold text-slate-700">{{ $paginator->firstItem() }}</span>–<span class="font-semibold text-slate-700">{{ $paginator->lastItem() }}</span>
                dari <span class="font-semibold text-slate-700">{{ $paginator->total() }}</span> data
            </p>

            <div class="flex items-center gap-1">
                {{-- Prev --}}
                @if ($paginator->onFirstPage())
                    <span class="inline-flex h-8 w-8 cursor-default items-center justify-center rounded-lg text-slate-300">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}"
                       class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition-colors">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    </a>
                @endif

                {{-- Nomor halaman --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="inline-flex h-8 items-center px-1 text-sm text-slate-400">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="inline-flex h-8 w-8 cursor-default items-center justify-center rounded-lg bg-brand-500 text-xs font-semibold text-white">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}"
                                   class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-xs font-medium text-slate-600 hover:bg-brand-50 hover:text-brand-700 hover:border-brand-200 transition-colors">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}"
                       class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition-colors">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                @else
                    <span class="inline-flex h-8 w-8 cursor-default items-center justify-center rounded-lg text-slate-300">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
