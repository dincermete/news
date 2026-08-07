@php
    $authLabel = 'mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3';
    $authInput = 'block w-full rounded-md border border-ink/10 bg-white px-3.5 py-3 text-sm text-ink placeholder:text-ink-3 focus:border-ink/30 focus:ring-0';

    // ?auth=login|register (LoginController/RegisterController'ın /giris,
    // /kayitol'dan yönlendirmesi) artık burada değil, Alpine tarafında
    // (authModal init()) okunuyor — bkz. public.js.
    $authRegisterHadErrors = old('name') !== null || $errors->has('name') || $errors->has('phone');
    $authInitialTab = $authRegisterHadErrors ? 'register' : 'login';
    $authRegisterRef = old('ref') ?? request()->query('ref');
@endphp

<div
    x-data="authModal({ initialOpen: @js($errors->any()), initialTab: @js($authInitialTab) })"
    x-show="open"
    x-cloak
    x-transition.opacity
    class="fixed inset-0 z-[80] flex items-center justify-center bg-ink/40 p-4 backdrop-blur-sm"
    @keydown.escape.window="close()"
    role="dialog"
    aria-modal="true"
    aria-label="Giriş yap veya üye ol"
>
    <div
        class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-pop"
        @click.outside="close()"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
    >
        <div class="flex items-center justify-between gap-3 border-b border-ink/5 px-5 py-4">
            <p class="font-display text-base font-semibold text-ink" x-text="tab === 'login' ? 'Giriş yap' : 'Üye ol'"></p>
            <button
                type="button"
                class="inline-flex size-8 items-center justify-center rounded-md text-ink-3 transition hover:bg-paper hover:text-ink"
                @click="close()"
                aria-label="Kapat"
            >
                <svg class="size-4.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="px-5 pt-4">
            <div class="grid grid-cols-2 gap-1 rounded-md bg-paper p-1">
                <button
                    type="button"
                    @click="tab = 'login'"
                    @class(['rounded px-3 py-2 text-sm font-semibold transition'])
                    :class="tab === 'login' ? 'bg-white text-ink shadow-soft' : 'text-ink-2 hover:text-ink'"
                >
                    Giriş yap
                </button>
                <button
                    type="button"
                    @click="tab = 'register'"
                    @class(['rounded px-3 py-2 text-sm font-semibold transition'])
                    :class="tab === 'register' ? 'bg-white text-ink shadow-soft' : 'text-ink-2 hover:text-ink'"
                >
                    Üye ol
                </button>
            </div>
        </div>

        {{-- Giriş yap --}}
        <div
            x-show="tab === 'login'"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-x-2"
            x-transition:enter-end="opacity-100 translate-x-0"
            class="px-5 pb-6 pt-4"
        >
            <a href="{{ route('auth.google.redirect') }}" class="flex w-full items-center justify-center gap-x-2.5 rounded-md border border-ink/10 bg-paper px-4 py-3 text-sm font-semibold text-ink transition hover:bg-ink/5 active:scale-[0.98]">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" aria-hidden="true">
                    <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/>
                    <path fill="#FF3D00" d="m6.306 14.691 6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z"/>
                    <path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238A11.91 11.91 0 0 1 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z"/>
                    <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303a12.04 12.04 0 0 1-4.087 5.571l.003-.002 6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/>
                </svg>
                Google ile giriş yap
            </a>

            <div class="mt-5 flex items-center gap-x-3 text-xs font-medium uppercase tracking-wide text-ink-3">
                <span class="h-px flex-1 bg-ink/10"></span>
                veya
                <span class="h-px flex-1 bg-ink/10"></span>
            </div>

            @if ($errors->any() && $authInitialTab === 'login')
                <div class="mt-4 rounded-md border border-brand-200 bg-brand-50 px-3.5 py-2.5 text-sm text-brand-800">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="post" action="{{ route('login.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label for="auth-login-email" class="{{ $authLabel }}">E-posta adresiniz *</label>
                    <input
                        id="auth-login-email"
                        type="email"
                        name="email"
                        value="{{ $authInitialTab === 'login' ? old('email') : '' }}"
                        required
                        autocomplete="email"
                        placeholder="Lütfen e-posta adresinizi yazınız"
                        class="{{ $authInput }}"
                    >
                </div>
                <div>
                    <label for="auth-login-password" class="{{ $authLabel }}">Şifreniz *</label>
                    <div class="relative">
                        <input
                            id="auth-login-password"
                            :type="showPassword ? 'text' : 'password'"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Lütfen şifrenizi yazınız"
                            class="{{ $authInput }} pr-11"
                        >
                        <button
                            type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-ink-3 transition hover:text-ink"
                            @click="showPassword = !showPassword"
                            :aria-label="showPassword ? 'Şifreyi gizle' : 'Şifreyi göster'"
                        >
                            <svg x-show="!showPassword" class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                            <svg x-show="showPassword" x-cloak class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                        </button>
                    </div>
                </div>

                <label class="flex cursor-pointer items-center gap-x-2.5 text-sm text-ink-2">
                    <input type="checkbox" name="remember" value="1" class="size-4 rounded border-ink/20 text-ink focus:ring-0">
                    Beni hatırla
                </label>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-md bg-gradient-to-b from-black to-[#363b3c] px-4 py-3.5 text-sm font-semibold text-white transition hover:scale-[1.01] active:scale-[0.98]">
                    Giriş yap
                </button>
            </form>
        </div>

        {{-- Üye ol --}}
        <div
            x-show="tab === 'register'"
            x-cloak
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-x-2"
            x-transition:enter-end="opacity-100 translate-x-0"
            class="max-h-[75vh] overflow-y-auto px-5 pb-6 pt-4"
        >
            <a href="{{ route('auth.google.redirect', array_filter(['ref' => $authRegisterRef])) }}" class="flex w-full items-center justify-center gap-x-2.5 rounded-md border border-ink/10 bg-paper px-4 py-3 text-sm font-semibold text-ink transition hover:bg-ink/5 active:scale-[0.98]">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" aria-hidden="true">
                    <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/>
                    <path fill="#FF3D00" d="m6.306 14.691 6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z"/>
                    <path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238A11.91 11.91 0 0 1 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z"/>
                    <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303a12.04 12.04 0 0 1-4.087 5.571l.003-.002 6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/>
                </svg>
                Google ile üye ol
            </a>

            <div class="mt-5 flex items-center gap-x-3 text-xs font-medium uppercase tracking-wide text-ink-3">
                <span class="h-px flex-1 bg-ink/10"></span>
                veya
                <span class="h-px flex-1 bg-ink/10"></span>
            </div>

            @if ($errors->any() && $authInitialTab === 'register')
                <div class="mt-4 rounded-md border border-brand-200 bg-brand-50 px-3.5 py-2.5 text-sm text-brand-800">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="post" action="{{ route('register.store') }}" class="mt-4 space-y-4">
                @csrf
                @if (! empty($authRegisterRef))
                    <input type="hidden" name="ref" value="{{ $authRegisterRef }}">
                @endif

                <div>
                    <label for="auth-register-name" class="{{ $authLabel }}">Ad soyad *</label>
                    <input
                        id="auth-register-name"
                        type="text"
                        name="name"
                        value="{{ $authInitialTab === 'register' ? old('name') : '' }}"
                        required
                        autocomplete="name"
                        placeholder="Lütfen adınızı ve soyadınızı yazınız"
                        class="{{ $authInput }}"
                    >
                </div>
                <div>
                    <label for="auth-register-email" class="{{ $authLabel }}">E-posta adresiniz *</label>
                    <input
                        id="auth-register-email"
                        type="email"
                        name="email"
                        value="{{ $authInitialTab === 'register' ? old('email') : '' }}"
                        required
                        autocomplete="email"
                        placeholder="Lütfen e-posta adresinizi yazınız"
                        class="{{ $authInput }}"
                    >
                </div>
                <div>
                    <label for="auth-register-phone" class="{{ $authLabel }}">Telefon <span class="font-normal normal-case tracking-normal text-ink-3">(opsiyonel)</span></label>
                    <input
                        id="auth-register-phone"
                        type="tel"
                        name="phone"
                        value="{{ $authInitialTab === 'register' ? old('phone') : '' }}"
                        autocomplete="tel"
                        placeholder="Lütfen telefon numaranızı yazınız"
                        class="{{ $authInput }}"
                    >
                </div>
                <div>
                    <label for="auth-register-password" class="{{ $authLabel }}">Şifreniz *</label>
                    <div class="relative">
                        <input
                            id="auth-register-password"
                            :type="showPasswordConfirm ? 'text' : 'password'"
                            name="password"
                            required
                            autocomplete="new-password"
                            placeholder="Lütfen şifrenizi yazınız"
                            class="{{ $authInput }} pr-11"
                        >
                        <button
                            type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-ink-3 transition hover:text-ink"
                            @click="showPasswordConfirm = !showPasswordConfirm"
                            :aria-label="showPasswordConfirm ? 'Şifreyi gizle' : 'Şifreyi göster'"
                        >
                            <svg x-show="!showPasswordConfirm" class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                            <svg x-show="showPasswordConfirm" x-cloak class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                        </button>
                    </div>
                </div>
                <div>
                    <label for="auth-register-password-confirmation" class="{{ $authLabel }}">Şifre tekrar *</label>
                    <input
                        id="auth-register-password-confirmation"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="Lütfen şifrenizi tekrar yazınız"
                        class="{{ $authInput }}"
                    >
                </div>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-md bg-gradient-to-b from-black to-[#363b3c] px-4 py-3.5 text-sm font-semibold text-white transition hover:scale-[1.01] active:scale-[0.98]">
                    Üye ol
                </button>
            </form>
        </div>
    </div>
</div>
