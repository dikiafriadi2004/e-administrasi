<?php

namespace App\Http\Requests\Auth;

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate using email or NIM.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $login = $this->string('email')->toString(); // field bernama 'email' tapi bisa berisi NIM
        $password = $this->string('password')->toString();

        // Deteksi apakah input adalah email atau NIM
        // NIM = tidak mengandung '@' dan hanya angka/alfanumerik
        $isEmail = Str::contains($login, '@');

        if ($isEmail) {
            $credentials = ['email' => $login, 'password' => $password];
        } else {
            // Cari user_id berdasarkan NIM
            $mahasiswa = Mahasiswa::where('nim', $login)->first();
            if (! $mahasiswa) {
                RateLimiter::hit($this->throttleKey());
                throw ValidationException::withMessages([
                    'email' => trans('auth.failed'),
                ]);
            }
            $credentials = ['id' => $mahasiswa->user_id, 'password' => $password];
        }

        // Cek is_active sebelum Auth::attempt
        if ($isEmail) {
            $user = User::where('email', $login)->first();
        } else {
            $user = User::find($credentials['id']);
        }

        if ($user && ! $user->is_active) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => 'Akun Anda telah dinonaktifkan. Hubungi administrator.',
            ]);
        }

        $attempted = $isEmail
            ? Auth::attempt(['email' => $login, 'password' => $password], $this->boolean('remember'))
            : Auth::attempt(['id' => $credentials['id'], 'password' => $password], $this->boolean('remember'));

        if (! $attempted) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
