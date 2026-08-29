
<div
    x-data="{
        notifications: [],
        add(type, message) {
            const id = Date.now();
            this.notifications.push({ id, type, message, visible: false });
            this.$nextTick(() => {
                const n = this.notifications.find(n => n.id === id);
                if (n) n.visible = true;
            });
            setTimeout(() => this.remove(id), 4500);
        },
        remove(id) {
            const n = this.notifications.find(n => n.id === id);
            if (n) {
                n.visible = false;
                setTimeout(() => {
                    this.notifications = this.notifications.filter(n => n.id !== id);
                }, 400);
            }
        }
    }"
    x-on:notify.window="add($event.detail.type, $event.detail.message)"
    x-on:livewire:navigated.window="notifications = []"
    class="fixed bottom-5 right-5 z-[9999] flex flex-col gap-2.5"
    style="max-width: 22rem;"
    aria-live="polite"
>
    <?php
        $icons = [
            'success' => '<svg class="h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>',
            'error'   => '<svg class="h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
            'warning' => '<svg class="h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>',
            'info'    => '<svg class="h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>',
        ];
    ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = [
        ['success', session('success')],
        ['error',   session('error')],
        ['warning', session('warning')],
        ['info',    session('info')],
    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$type, $msg]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($msg): ?>
            <div
                x-data="{ visible: false }"
                x-init="$nextTick(() => { visible = true; setTimeout(() => { visible = false; setTimeout(() => $el.remove(), 400); }, 4500); })"
                x-show="visible"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'flex items-start gap-3 rounded-2xl border px-4 py-3 shadow-lg text-sm',
                    'bg-emerald-50 border-emerald-200 text-emerald-800' => $type === 'success',
                    'bg-red-50 border-red-200 text-red-800'             => $type === 'error',
                    'bg-amber-50 border-amber-200 text-amber-800'       => $type === 'warning',
                    'bg-sky-50 border-sky-200 text-sky-800'             => $type === 'info',
                ]); ?>"
            >
                <span class="mt-0.5 <?php if($type === 'success'): ?> text-emerald-600 <?php elseif($type === 'error'): ?> text-red-500 <?php elseif($type === 'warning'): ?> text-amber-600 <?php else: ?> text-sky-600 <?php endif; ?>">
                    <?php echo $icons[$type] ?? $icons['info']; ?>

                </span>
                <span class="flex-1 leading-snug"><?php echo e($msg); ?></span>
                <button @click="$el.closest('[x-data]').remove()"
                        class="ml-1 shrink-0 opacity-40 hover:opacity-70 transition-opacity leading-none text-lg">&times;</button>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

    
    <template x-for="n in notifications" :key="n.id">
        <div
            x-show="n.visible"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-2 scale-95"
            :class="{
                'flex items-start gap-3 rounded-2xl border px-4 py-3 shadow-lg text-sm': true,
                'bg-emerald-50 border-emerald-200 text-emerald-800': n.type === 'success',
                'bg-red-50 border-red-200 text-red-800':             n.type === 'error',
                'bg-amber-50 border-amber-200 text-amber-800':       n.type === 'warning',
                'bg-sky-50 border-sky-200 text-sky-800':             n.type === 'info',
            }"
        >
            <span class="mt-0.5"
                  :class="{
                      'text-emerald-600': n.type === 'success',
                      'text-red-500':     n.type === 'error',
                      'text-amber-600':   n.type === 'warning',
                      'text-sky-600':     n.type === 'info',
                  }"
                  x-html="
                    n.type === 'success'
                      ? '<svg class=\'h-4 w-4 shrink-0\' xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'M22 11.08V12a10 10 0 1 1-5.93-9.14\'/><path d=\'m9 11 3 3L22 4\'/></svg>'
                      : n.type === \'error\'
                      ? '<svg class=\'h-4 w-4 shrink-0\' xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><circle cx=\'12\' cy=\'12\' r=\'10\'/><line x1=\'15\' y1=\'9\' x2=\'9\' y2=\'15\'/><line x1=\'9\' y1=\'9\' x2=\'15\' y2=\'15\'/></svg>'
                      : n.type === \'warning\'
                      ? '<svg class=\'h-4 w-4 shrink-0\' xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z\'/><path d=\'M12 9v4\'/><path d=\'M12 17h.01\'/></svg>'
                      : '<svg class=\'h-4 w-4 shrink-0\' xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><circle cx=\'12\' cy=\'12\' r=\'10\'/><path d=\'M12 16v-4\'/><path d=\'M12 8h.01\'/></svg>'
                  ">
            </span>
            <span class="flex-1 leading-snug" x-text="n.message"></span>
            <button @click="remove(n.id)" class="ml-1 shrink-0 opacity-40 hover:opacity-70 transition-opacity text-lg leading-none">&times;</button>
        </div>
    </template>
</div>
<?php /**PATH E:\MUDK Project\project\e-administrasi\resources\views/components/flash-message.blade.php ENDPATH**/ ?>