<x-filament-panels::page>
    <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <header class="mb-4">
            <h2 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">
                {{ __('Your passkeys') }}
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ __('Passkeys let you sign in without a password using your device biometrics or a security key.') }}
            </p>
        </header>

        @include('filament-passkeys::passkey-manager', ['passkeys' => $passkeys])
    </div>
</x-filament-panels::page>
