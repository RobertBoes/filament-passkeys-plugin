@props([
    'passkeys' => [],
    'scriptSrc' => null,
])

@php
    $scriptSrc ??= config('filament-passkeys.client_script_src');
@endphp

<div x-data="filamentPasskeysManager()" class="fi-filament-passkeys-manager space-y-4">
    <div class="flex items-end gap-2">
        <div class="flex-1">
            <label for="passkey-name" class="fi-fo-field-wrp-label inline-flex items-center gap-x-3 text-sm font-medium leading-6 text-gray-950 dark:text-white">
                {{ __('Name this passkey') }}
            </label>
            <input
                id="passkey-name"
                type="text"
                x-model="name"
                x-bind:disabled="loading"
                placeholder="{{ __('e.g. MacBook Touch ID') }}"
                class="fi-input block w-full rounded-lg border-none bg-white py-1.5 text-base text-gray-950 shadow-sm ring-1 ring-gray-950/10 transition duration-75 placeholder:text-gray-400 focus:ring-2 focus:ring-primary-600 disabled:opacity-70 dark:bg-white/5 dark:text-white dark:ring-white/20 dark:focus:ring-primary-500 sm:text-sm sm:leading-6"
            />
        </div>
        <button
            type="button"
            x-on:click="addPasskey"
            x-bind:disabled="loading || !name"
            class="fi-btn fi-btn-color-primary fi-btn-size-md inline-flex items-center justify-center gap-1.5 rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 disabled:opacity-70"
        >
            <span x-text="loading ? @js(__('Adding…')) : @js(__('Add passkey'))"></span>
        </button>
    </div>

    <p x-show="error" x-text="error" class="text-sm text-danger-600 dark:text-danger-400"></p>
    <p x-show="success" x-text="success" class="text-sm text-success-600 dark:text-success-400"></p>

    @if (! empty($passkeys))
        <ul class="divide-y divide-gray-100 rounded-lg ring-1 ring-gray-950/10 dark:divide-white/5 dark:ring-white/10">
            @foreach ($passkeys as $passkey)
                <li class="flex items-center justify-between gap-4 px-4 py-3">
                    <div>
                        <p class="text-sm font-medium text-gray-950 dark:text-white">{{ $passkey->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('Added :date', ['date' => $passkey->created_at?->diffForHumans()]) }}
                        </p>
                    </div>
                    <form
                        method="POST"
                        action="{{ url('/user/passkeys/' . $passkey->getKey()) }}"
                        onsubmit="return confirm(@js(__('Remove this passkey?')))"
                    >
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm font-medium text-danger-600 hover:text-danger-500 dark:text-danger-400">
                            {{ __('Remove') }}
                        </button>
                    </form>
                </li>
            @endforeach
        </ul>
    @endif
</div>

@once
    @push('scripts')
        <script type="module">
            import { Passkeys, PasskeyExistsError, UserCancelledError } from {!! json_encode($scriptSrc) !!};

            window.filamentPasskeysManager = () => ({
                name: '',
                loading: false,
                error: null,
                success: null,
                async addPasskey() {
                    this.loading = true;
                    this.error = null;
                    this.success = null;
                    try {
                        await Passkeys.register({ name: this.name });
                        this.success = @js(__('Passkey added.'));
                        this.name = '';
                        // Reload so the freshly-stored passkey shows in the list.
                        setTimeout(() => window.location.reload(), 400);
                    } catch (e) {
                        if (e instanceof UserCancelledError) {
                            return;
                        }
                        if (e instanceof PasskeyExistsError) {
                            this.error = @js(__('That device already has a passkey for this account.'));
                            return;
                        }
                        this.error = e?.message ?? @js(__('Could not add passkey.'));
                    } finally {
                        this.loading = false;
                    }
                },
            });
        </script>
    @endpush
@endonce
