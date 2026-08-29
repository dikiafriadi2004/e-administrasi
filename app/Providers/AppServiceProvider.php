<?php

namespace App\Providers;

use App\Models\PengajuanJudul;
use App\Models\PengajuanSurat;
use App\Policies\PengajuanJudulPolicy;
use App\Policies\PengajuanSuratPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Gunakan Tailwind pagination view
        Paginator::useTailwind();

        // Policy
        Gate::policy(PengajuanSurat::class, PengajuanSuratPolicy::class);
        Gate::policy(PengajuanJudul::class, PengajuanJudulPolicy::class);

        // Gates per role
        Gate::define('is-mahasiswa', fn ($user) => $user->role === 'mahasiswa' && $user->is_active);
        Gate::define('is-admin', fn ($user) => $user->role === 'admin');
        Gate::define('is-kaprodi', fn ($user) => $user->role === 'kaprodi');
    }
}
