@props(['current' => 10, 'options' => [5, 10, 25, 50, 100]])

<div class="flex items-center gap-2 text-xs text-slate-500">
    <span>Tampilkan</span>
    <select onchange="window.location.href = updateQueryParam('perPage', this.value)"
            class="rounded-lg border-slate-200 py-1 pl-2 pr-7 text-xs shadow-sm focus:border-brand-400 focus:ring-brand-400">
        @foreach ($options as $opt)
            <option value="{{ $opt }}" {{ (int)$current === (int)$opt ? 'selected' : '' }}>{{ $opt }}</option>
        @endforeach
    </select>
    <span>data per halaman</span>
</div>

@once
@push('scripts')
<script>
function updateQueryParam(key, value) {
    const url = new URL(window.location.href);
    url.searchParams.set(key, value);
    url.searchParams.set('page', 1); // reset to page 1
    return url.toString();
}
</script>
@endpush
@endonce
