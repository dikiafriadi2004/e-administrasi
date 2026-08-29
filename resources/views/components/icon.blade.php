@props(['name', 'class' => 'h-5 w-5'])

@php
    $iconPath = base_path("node_modules/lucide-static/icons/{$name}.svg");
    if (file_exists($iconPath)) {
        $svg = file_get_contents($iconPath);
        // Hapus komentar lisensi
        $svg = preg_replace('/<!--.*?-->/s', '', $svg);
        // Ganti atribut width/height/class bawaan dengan Tailwind class
        $svg = preg_replace('/\s+width="[^"]*"/', '', $svg);
        $svg = preg_replace('/\s+height="[^"]*"/', '', $svg);
        $svg = preg_replace('/\s+class="[^"]*"/', '', $svg);
        // Inject class Tailwind
        $svg = str_replace('<svg', '<svg class="' . $class . '"', $svg);
        $svg = trim($svg);
    } else {
        // Fallback: kotak kosong jika icon tidak ditemukan
        $svg = '<svg class="' . $class . '" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>';
    }
@endphp

{!! $svg !!}
