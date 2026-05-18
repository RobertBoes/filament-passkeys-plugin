@props([
    'label' => 'Sign in with passkey',
    'scriptSrc' => 'https://esm.sh/@laravel/passkeys@latest',
])

<div
    x-data="filamentPasskeysLogin()"
    x-cloak
    x-show="supported"
    class="fi-filament-passkeys-login mt-6"
>
    <div class="relative flex items-center justify-center my-4">
        <div class="absolute inset-0 flex items-center" aria-hidden="true">
            <div class="w-full border-t border-gray-200 dark:border-white/10"></div>
        </div>
        <div class="relative bg-white dark:bg-gray-900 px-3 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
            {{ __('or') }}
        </div>
    </div>

    <button
        type="button"
        x-on:click="signIn"
        x-bind:disabled="loading"
        class="fi-btn fi-btn-color-gray fi-btn-size-md fi-color fi-color-gray fi-size-md relative inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-950 shadow-sm hover:bg-gray-50 disabled:opacity-70 dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:bg-white/10"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
        </svg>
        <span x-text="loading ? @js(__('Signing in…')) : @js($label)"></span>
    </button>

    <p x-show="error" x-text="error" class="mt-2 text-sm text-danger-600 dark:text-danger-400"></p>
</div>

@once
    @push('scripts')
        <script type="module">
            import { Passkeys, UserCancelledError } from {!! json_encode($scriptSrc) !!};

            window.filamentPasskeysLogin = () => ({
                loading: false,
                error: null,
                supported: true,
                init() {
                    this.supported = Passkeys.isSupported();
                },
                async signIn() {
                    this.loading = true;
                    this.error = null;
                    try {
                        const result = await Passkeys.verify();
                        const redirect = result?.redirect ?? null;
                        if (redirect) {
                            window.location.href = redirect;
                            return;
                        }
                        window.location.reload();
                    } catch (e) {
                        if (e instanceof UserCancelledError) {
                            return;
                        }
                        this.error = e?.message ?? @js(__('Passkey sign in failed.'));
                    } finally {
                        this.loading = false;
                    }
                },
            });
        </script>
    @endpush
@endonce
