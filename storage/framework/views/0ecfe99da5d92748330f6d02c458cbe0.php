<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e(config('app.name', 'E-Administrasi')); ?></title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="font-sans antialiased">

<div class="min-h-screen lg:grid lg:grid-cols-2">

    
    <div class="hidden lg:flex flex-col justify-between bg-gradient-to-br from-brand-500 to-brand-700 px-12 py-16">
        
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm">
                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                    <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                </svg>
            </div>
            <span class="text-base font-bold text-white">E-Administrasi Prodi</span>
        </div>

        
        <div class="space-y-5">
            <h2 class="text-3xl font-bold leading-snug text-white">
                Kelola administrasi<br>akademik lebih mudah.
            </h2>
            <p class="text-base text-brand-100 leading-relaxed">
                Pengajuan judul, seminar, sidang, dan surat keterangan aktif kuliah — semuanya dalam satu platform digital.
            </p>

            <ul class="mt-6 space-y-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = [
                    'Pengajuan online tanpa antre',
                    'Status pengajuan real-time',
                    'Download surat kapan saja',
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $text): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <li class="flex items-center gap-3 text-sm text-brand-50">
                        <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-white/20">
                            <svg class="h-3 w-3 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m20 6-11 11-5-5"/></svg>
                        </div>
                        <?php echo e($text); ?>

                    </li>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </ul>
        </div>

        <p class="text-xs text-brand-200">
            &copy; <?php echo e(date('Y')); ?> E-Administrasi Prodi
        </p>
    </div>

    
    <div class="flex min-h-screen flex-col items-center justify-center bg-slate-50 px-6 py-12 lg:min-h-0">
        
        <div class="mb-8 flex items-center gap-3 lg:hidden">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-500">
                <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                    <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                </svg>
            </div>
            <span class="text-base font-bold text-slate-800">E-Administrasi Prodi</span>
        </div>

        <div class="w-full max-w-sm">
            <?php echo e($slot); ?>

        </div>
    </div>

</div>
</body>
</html>
<?php /**PATH E:\MUDK Project\project\e-administrasi\resources\views/layouts/guest.blade.php ENDPATH**/ ?>