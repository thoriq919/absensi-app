<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}; ?>

<div class="flex flex-col gap-6 bg-white dark:bg-slate-800 p-8 rounded-xl shadow-lg max-w-md mx-auto">
    <!-- Theme Toggle Button -->
    <div class="absolute top-6 right-6">
        <flux:button 
            x-data
            x-on:click="$flux.appearance = $flux.appearance === 'dark' ? 'light' : 'dark'"
            variant="ghost" 
            size="sm"
            square
            aria-label="Toggle theme"
        >
            <flux:icon.sun x-show="$flux.appearance === 'light'" class="h-5 w-5 text-amber-500" />
            <flux:icon.moon x-show="$flux.appearance === 'dark'" class="h-5 w-5 text-indigo-400" />
        </flux:button>
    </div>

    <!-- Logo and Header -->
    <div class="text-center mb-2">
        <div class="flex justify-center mb-4">
            <flux:icon.shield-check class="h-12 w-12 text-indigo-600 dark:text-indigo-400" />
        </div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Welcome back') }}</h2>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ __('Enter your credentials to access your account') }}</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="text-center text-sm font-medium" :status="session('status')" />

    <form wire:submit="login" class="flex flex-col gap-5">
        <!-- Email Address -->
        <div>
            <flux:input
                wire:model="email"
                :label="__('Email address')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="you@example.com"
                class="dark:bg-slate-700 dark:border-slate-600 dark:text-white"
            />
        </div>

        <!-- Password -->
        <div class="space-y-1">
            <div class="flex justify-between items-center">
                <flux:label for="password" class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('Password') }}</flux:label>
                @if (Route::has('password.request'))
                    <flux:link class="text-xs font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400" :href="route('password.request')" wire:navigate>
                        {{ __('Forgot password?') }}
                    </flux:link>
                @endif
            </div>
            <flux:input
                wire:model="password"
                id="password"
                type="password"
                required
                autocomplete="current-password"
                placeholder="••••••••"
                class="dark:bg-slate-700 dark:border-slate-600 dark:text-white"
            />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center">
            <flux:checkbox 
                wire:model="remember" 
                :label="__('Keep me signed in')" 
                class="text-indigo-600 dark:text-indigo-400"
            />
        </div>

        <div class="pt-2">
            <flux:button 
                variant="primary" 
                type="submit" 
                class="w-full bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600"
            >
                {{ __('Sign in') }}
            </flux:button>
        </div>
    </form>
</div>