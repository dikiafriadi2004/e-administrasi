<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['name', 'class' => 'h-5 w-5']));

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

foreach (array_filter((['name', 'class' => 'h-5 w-5']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php
    // Coba dari public/icons dulu (production), fallback ke node_modules (local dev)
    $iconPath = public_path("icons/{$name}.svg");
    if (!file_exists($iconPath)) {
        $iconPath = base_path("node_modules/lucide-static/icons/{$name}.svg");
    }

    if (file_exists($iconPath)) {
        $svg = file_get_contents($iconPath);
        $svg = preg_replace('/<!--.*?-->/s', '', $svg);
        $svg = preg_replace('/\s+width="[^"]*"/', '', $svg);
        $svg = preg_replace('/\s+height="[^"]*"/', '', $svg);
        $svg = preg_replace('/\s+class="[^"]*"/', '', $svg);
        $svg = str_replace('<svg', '<svg class="' . $class . '"', $svg);
        $svg = trim($svg);
    } else {
        $svg = '<svg class="' . $class . '" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>';
    }
?>
<?php echo $svg; ?>

<?php /**PATH E:\MUDK Project\project\e-administrasi\resources\views/components/icon.blade.php ENDPATH**/ ?>