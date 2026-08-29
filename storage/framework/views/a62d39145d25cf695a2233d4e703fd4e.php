<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['label', 'value', 'icon' => null, 'color' => 'brand']));

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

foreach (array_filter((['label', 'value', 'icon' => null, 'color' => 'brand']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$colorMap = [
    'brand'  => ['bg' => 'bg-brand-50',   'icon' => 'text-brand-600',   'ring' => 'ring-brand-100'],
    'indigo' => ['bg' => 'bg-indigo-50',  'icon' => 'text-indigo-600',  'ring' => 'ring-indigo-100'],
    'blue'   => ['bg' => 'bg-sky-50',     'icon' => 'text-sky-600',     'ring' => 'ring-sky-100'],
    'green'  => ['bg' => 'bg-emerald-50', 'icon' => 'text-emerald-600', 'ring' => 'ring-emerald-100'],
    'yellow' => ['bg' => 'bg-amber-50',   'icon' => 'text-amber-600',   'ring' => 'ring-amber-100'],
    'red'    => ['bg' => 'bg-red-50',     'icon' => 'text-red-500',     'ring' => 'ring-red-100'],
];
$c = $colorMap[$color] ?? $colorMap['brand'];
?>

<div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
    <div class="flex items-start justify-between gap-3">
        <div class="flex-1 min-w-0">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500"><?php echo e($label); ?></p>
            <p class="mt-2 text-3xl font-bold tracking-tight text-slate-800"><?php echo e(number_format($value)); ?></p>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($icon): ?>
            <div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ring-1', $c['bg'], $c['ring']]); ?>">
                <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => $icon,'class' => \Illuminate\Support\Arr::toCssClasses(['h-5 w-5', $c['icon']])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\Illuminate\Support\Arr::toCssClasses(['h-5 w-5', $c['icon']]))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH E:\MUDK Project\project\e-administrasi\resources\views/components/stat-card.blade.php ENDPATH**/ ?>