@props(['name', 'class' => 'h-5 w-5'])
@php
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
@endphp
{!! $svg !!}
