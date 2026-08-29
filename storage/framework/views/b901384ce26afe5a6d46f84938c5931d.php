<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['current' => 10, 'options' => [5, 10, 25, 50, 100]]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['current' => 10, 'options' => [5, 10, 25, 50, 100]]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="flex items-center gap-2 text-xs text-slate-500">
    <span>Tampilkan</span>
    <select onchange="window.location.href = updateQueryParam('perPage', this.value)"
            class="rounded-lg border-slate-200 py-1 pl-2 pr-7 text-xs shadow-sm focus:border-brand-400 focus:ring-brand-400">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <option value="<?php echo e($opt); ?>" <?php echo e((int)$current === (int)$opt ? 'selected' : ''); ?>><?php echo e($opt); ?></option>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </select>
    <span>data per halaman</span>
</div>

<?php if (! $__env->hasRenderedOnce('809e079f-20ee-44ab-8d4d-7cb5d9ba107e')): $__env->markAsRenderedOnce('809e079f-20ee-44ab-8d4d-7cb5d9ba107e'); ?>
<?php $__env->startPush('scripts'); ?>
<script>
function updateQueryParam(key, value) {
    const url = new URL(window.location.href);
    url.searchParams.set(key, value);
    url.searchParams.set('page', 1); // reset to page 1
    return url.toString();
}
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH E:\MUDK Project\project\e-administrasi\resources\views/components/per-page-selector.blade.php ENDPATH**/ ?>