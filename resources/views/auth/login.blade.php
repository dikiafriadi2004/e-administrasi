<x-guest-layout>

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">Selamat datang 👋</h1>
        <p class="mt-1.5 text-sm text-slate-500">Masuk ke akun Anda untuk melanjutkan</p>
    </div>

    @if (session('status'))
        <div class="mb-5 flex items-center gap-2 rounded-xl border border-brand-200 bg-brand-50 px-3 py-2.5 text-sm text-brand-700">
            <svg class="h-4 w-4 shrink-0 text-brand-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        {{-- Email / NIM --}}
        <div class="space-y-1.5">
            <label for="email" class="block text-sm font-medium text-slate-700">Email atau NIM</label>
            <input id="email" type="text" name="email"
                   value="{{ old('email') }}"
                   required autofocus autocomplete="username"
                   placeholder="email@kampus.ac.id atau NIM"
                   class="block w-full rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-brand-400 focus:ring-brand-400 transition-colors @error('email') border-red-300 @enderror" />
            @error('email')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div class="space-y-1.5">
            <div class="flex items-center justify-between">
                <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs text-brand-600 hover:text-brand-700 hover:underline">
                        Lupa password?
                    </a>
                @endif
            </div>
            <input id="password" type="password" name="password"
                   required autocomplete="current-password"
                   placeholder="••••••••"
                   class="block w-full rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-brand-400 focus:ring-brand-400 transition-colors @error('password') border-red-300 @enderror" />
            @error('password')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Remember me --}}
        <div class="flex items-center gap-2">
            <input id="remember_me" type="checkbox" name="remember"
                   class="h-4 w-4 rounded border-slate-300 text-brand-600 shadow-sm focus:ring-brand-400" />
            <label for="remember_me" class="text-sm text-slate-600">Ingat saya</label>
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-brand-500 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:ring-offset-2 transition-colors active:bg-brand-700">
            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" x2="3" y1="12" y2="12"/></svg>
            Masuk
        </button>
    </form>

</x-guest-layout>
