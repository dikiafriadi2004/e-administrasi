<x-app-layout>
    <x-slot name="title">Profil Saya</x-slot>

    <div class="mx-auto max-w-xl space-y-5">

        {{-- Info Akun --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-base font-semibold text-slate-800">Informasi Akun</h2>

            <dl class="space-y-3 text-sm">
                <div class="grid grid-cols-3 gap-2">
                    <dt class="font-medium text-slate-500">Nama</dt>
                    <dd class="col-span-2 text-slate-800">{{ $user->name }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <dt class="font-medium text-slate-500">Email</dt>
                    <dd class="col-span-2 text-slate-800">{{ $user->email }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <dt class="font-medium text-slate-500">Role</dt>
                    <dd class="col-span-2 capitalize text-slate-800">{{ $user->role }}</dd>
                </div>
                @if ($user->mahasiswa)
                    <div class="grid grid-cols-3 gap-2">
                        <dt class="font-medium text-slate-500">NIM</dt>
                        <dd class="col-span-2 font-mono text-slate-800">{{ $user->mahasiswa->nim }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <dt class="font-medium text-slate-500">Angkatan</dt>
                        <dd class="col-span-2 text-slate-800">{{ $user->mahasiswa->angkatan }}</dd>
                    </div>
                @endif
                <div class="grid grid-cols-3 gap-2">
                    <dt class="font-medium text-slate-500">Akun Dibuat</dt>
                    <dd class="col-span-2 text-slate-400">{{ $user->created_at->format('d M Y') }}</dd>
                </div>
            </dl>
        </div>

        {{-- Ganti Password --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-1 text-base font-semibold text-slate-800">Ganti Password</h2>
            <p class="mb-5 text-xs text-slate-400">Gunakan password yang kuat dan minimal 8 karakter.</p>

            @if (session('status') === 'password-updated')
                <div class="mb-4 flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    <svg class="h-4 w-4 shrink-0 text-emerald-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>
                    Password berhasil diperbarui.
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="current_password" value="Password Saat Ini *" />
                    <x-text-input id="current_password"
                                  name="current_password"
                                  type="password"
                                  class="mt-1 block w-full"
                                  autocomplete="current-password" />
                    @if ($errors->updatePassword->has('current_password'))
                        <p class="mt-1 text-xs text-red-600">
                            {{ $errors->updatePassword->first('current_password') }}
                        </p>
                    @endif
                </div>

                <div>
                    <x-input-label for="password" value="Password Baru *" />
                    <x-text-input id="password"
                                  name="password"
                                  type="password"
                                  class="mt-1 block w-full"
                                  autocomplete="new-password" />
                    @if ($errors->updatePassword->has('password'))
                        <p class="mt-1 text-xs text-red-600">
                            {{ $errors->updatePassword->first('password') }}
                        </p>
                    @endif
                </div>

                <div>
                    <x-input-label for="password_confirmation" value="Konfirmasi Password Baru *" />
                    <x-text-input id="password_confirmation"
                                  name="password_confirmation"
                                  type="password"
                                  class="mt-1 block w-full"
                                  autocomplete="new-password" />
                </div>

                <div class="flex justify-end pt-1">
                    <x-primary-button>Simpan Password Baru</x-primary-button>
                </div>
            </form>
        </div>

        {{-- Info tambahan untuk mahasiswa --}}
        @if ($user->role === 'mahasiswa')
            <div class="rounded-2xl border border-brand-200 bg-brand-50 p-4 text-sm text-brand-800">
                <p class="mb-1 flex items-center gap-2 font-semibold">
                    <svg class="h-4 w-4 text-brand-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                    Tips keamanan akun
                </p>
                <p class="text-brand-700">Password default saat akun dibuat adalah NIM kamu. Segera ganti password untuk keamanan akun.</p>
            </div>
        @endif

    </div>
</x-app-layout>
