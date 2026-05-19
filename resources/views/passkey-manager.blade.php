@props([
    'passkeys' => [],
])

@php
    $blockGap = 'calc(var(--spacing) * 6)';
    $itemGap = 'calc(var(--spacing) * 2)';

    $panelId = filament()->getCurrentPanel()->getId();
    $confirmPasswordUrl = route("filament.{$panelId}.filament-passkeys.confirm-password.store");
    $confirmPasswordStatusUrl = route("filament.{$panelId}.filament-passkeys.confirm-password.status");
    $deletePasskeyBaseUrl = url('/user/passkeys');

    $passkeysCollection = $passkeys instanceof \Illuminate\Support\Collection
        ? $passkeys
        : collect($passkeys);

    $initialPasskeys = $passkeysCollection
        ->map(fn ($passkey) => [
            'id' => $passkey->getKey(),
            'name' => $passkey->name,
            'addedLabel' => __('Added :date', ['date' => $passkey->created_at?->diffForHumans()]),
        ])
        ->values()
        ->all();
@endphp

<div
    x-data="filamentPasskeysManager({
        initialPasskeys: @js($initialPasskeys),
        confirmPasswordUrl: @js($confirmPasswordUrl),
        confirmPasswordStatusUrl: @js($confirmPasswordStatusUrl),
        deletePasskeyBaseUrl: @js($deletePasskeyBaseUrl),
        csrfToken: @js(csrf_token()),
        labels: {
            adding: @js(__('Adding…')),
            add: @js(__('Add passkey')),
            added: @js(__('Passkey added.')),
            exists: @js(__('That device already has a passkey for this account.')),
            failure: @js(__('Could not add passkey.')),
            notSupported: @js(__('Your browser does not support passkeys. Try a recent version of Chrome, Safari, Edge, or Firefox.')),
            notSupportedHeading: @js(__('Passkeys not supported')),
            invalidDomain: @js(__('Passkeys cannot be used on this domain. Make sure you are on the same origin as the app.')),
            confirmHeading: @js(__('Confirm your password')),
            confirmDescription: @js(__('For your security, please confirm your password to continue.')),
            passwordLabel: @js(__('Password')),
            confirmAction: @js(__('Confirm')),
            cancel: @js(__('Cancel')),
            invalidPassword: @js(__('The password is incorrect.')),
            networkError: @js(__('Could not verify password. Please try again.')),
            removeHeading: @js(__('Remove passkey?')),
            removeDescription: @js(__('You will no longer be able to sign in with this passkey.')),
            removeAction: @js(__('Remove')),
            removeFailure: @js(__('Could not remove passkey.')),
            removed: @js(__('Passkey removed.')),
            addedJustNow: @js(__('Added just now')),
        },
    })"
    class="fi-filament-passkeys-manager"
    style="display: flex; flex-direction: column; gap: {{ $blockGap }};"
>
    <template x-if="passkeys.length > 0">
        <ul style="display: flex; flex-direction: column; gap: {{ $itemGap }}; list-style: none; padding: 0; margin: 0;">
            <template x-for="passkey in passkeys" x-bind:key="passkey.id">
                <li style="display: flex; align-items: center; justify-content: space-between; gap: {{ $itemGap }};">
                    <div style="display: flex; align-items: center; gap: {{ $itemGap }}; min-width: 0;">
                        <x-filament::icon
                            icon="heroicon-o-key"
                            class="fi-icon"
                        />
                        <div style="min-width: 0;">
                            <p class="fi-fo-field-label" x-text="passkey.name"></p>
                            <p style="font-size: var(--text-xs);" x-text="passkey.addedLabel"></p>
                        </div>
                    </div>
                    <x-filament::button
                        tag="button"
                        type="button"
                        color="danger"
                        size="sm"
                        outlined
                        x-on:click="confirmDelete(passkey)"
                    >
                        {{ __('Remove') }}
                    </x-filament::button>
                </li>
            </template>
        </ul>
    </template>

    <template x-if="passkeys.length === 0">
        <x-filament::empty-state
            :heading="__('No passkeys yet')"
            :description="__('Add one below to sign in without a password.')"
            icon="heroicon-o-key"
            compact
        />
    </template>

    <template x-if="! supported">
        <div class="fi-callout fi-color fi-color-warning" role="alert">
            <x-filament::icon icon="heroicon-o-exclamation-triangle" class="fi-callout-icon" />
            <div class="fi-callout-main">
                <div class="fi-callout-text">
                    <h4 class="fi-callout-heading" x-text="labels.notSupportedHeading"></h4>
                    <p class="fi-callout-description" x-text="labels.notSupported"></p>
                </div>
            </div>
        </div>
    </template>

    <div x-show="supported" style="display: flex; flex-direction: column; gap: {{ $itemGap }};">
        <label for="passkey-name" class="fi-fo-field-label">
            {{ __('Add a new passkey') }}
        </label>

        <div style="display: flex; gap: {{ $itemGap }};">
            <div style="flex: 1;">
                <x-filament::input.wrapper>
                    <x-filament::input
                        id="passkey-name"
                        type="text"
                        x-model="name"
                        x-bind:disabled="loading || !supported"
                        :placeholder="__('e.g. MacBook Touch ID')"
                    />
                </x-filament::input.wrapper>
            </div>

            <x-filament::button
                tag="button"
                type="button"
                x-on:click="promptPassword"
                x-bind:disabled="loading || !name || !supported"
                icon="heroicon-m-plus"
            >
                <span x-text="loading ? labels.adding : labels.add"></span>
            </x-filament::button>
        </div>
    </div>

    <template x-if="error">
        <div class="fi-callout fi-color fi-color-danger" role="alert">
            <x-filament::icon icon="heroicon-o-x-circle" class="fi-callout-icon" />
            <div class="fi-callout-main">
                <div class="fi-callout-text">
                    <p class="fi-callout-description" x-text="error"></p>
                </div>
            </div>
        </div>
    </template>

    <template x-if="success">
        <div class="fi-callout fi-color fi-color-success" role="status">
            <x-filament::icon icon="heroicon-o-check-circle" class="fi-callout-icon" />
            <div class="fi-callout-main">
                <div class="fi-callout-text">
                    <p class="fi-callout-description" x-text="success"></p>
                </div>
            </div>
        </div>
    </template>

    @php
        $authUser = filament()->auth()->user();
        $usernameValue = $authUser?->email ?? $authUser?->getAuthIdentifier() ?? '';
    @endphp

    <x-filament::modal
        id="filament-passkeys-confirm-password"
        width="md"
        icon="heroicon-o-shield-check"
    >
        <x-slot name="heading">
            <span x-text="labels.confirmHeading"></span>
        </x-slot>

        <x-slot name="description">
            <span x-text="labels.confirmDescription"></span>
        </x-slot>

        <form
            id="filament-passkeys-confirm-password-form"
            x-on:submit.prevent="confirmPassword"
            style="display: flex; flex-direction: column; gap: {{ $itemGap }};"
        >
            <input
                type="text"
                name="username"
                autocomplete="username"
                value="{{ $usernameValue }}"
                readonly
                aria-hidden="true"
                tabindex="-1"
                style="position: absolute; left: -9999px; width: 1px; height: 1px; opacity: 0;"
            >

            <label for="filament-passkeys-confirm-password-input" class="fi-fo-field-label">
                <span x-text="labels.passwordLabel"></span>
            </label>

            <x-filament::input.wrapper>
                <x-filament::input
                    id="filament-passkeys-confirm-password-input"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    x-model="password"
                    x-bind:disabled="confirming"
                />
            </x-filament::input.wrapper>

            <template x-if="passwordError">
                <div class="fi-callout fi-color fi-color-danger" role="alert">
                    <x-filament::icon icon="heroicon-o-x-circle" class="fi-callout-icon" />
                    <div class="fi-callout-main">
                        <div class="fi-callout-text">
                            <p class="fi-callout-description" x-text="passwordError"></p>
                        </div>
                    </div>
                </div>
            </template>
        </form>

        <x-slot name="footerActions">
            <x-filament::button
                tag="button"
                type="submit"
                form-id="filament-passkeys-confirm-password-form"
                x-bind:disabled="confirming || !password"
            >
                <span x-text="labels.confirmAction"></span>
            </x-filament::button>

            <x-filament::button
                tag="button"
                type="button"
                color="gray"
                x-on:click="$dispatch('close-modal', { id: modalId })"
                x-bind:disabled="confirming"
            >
                <span x-text="labels.cancel"></span>
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

    <x-filament::modal
        id="filament-passkeys-confirm-delete"
        width="md"
        icon="heroicon-o-trash"
        icon-color="danger"
    >
        <x-slot name="heading">
            <span x-text="labels.removeHeading"></span>
        </x-slot>

        <x-slot name="description">
            <span>
                <span x-text="labels.removeDescription"></span>
                <template x-if="passkeyToDelete">
                    <strong x-text="' (' + passkeyToDelete.name + ')'"></strong>
                </template>
            </span>
        </x-slot>

        <x-slot name="footerActions">
            <x-filament::button
                tag="button"
                type="button"
                color="danger"
                x-on:click="beginDelete"
                x-bind:disabled="loading"
            >
                <span x-text="labels.removeAction"></span>
            </x-filament::button>

            <x-filament::button
                tag="button"
                type="button"
                color="gray"
                x-on:click="$dispatch('close-modal', { id: deleteModalId })"
                x-bind:disabled="loading"
            >
                <span x-text="labels.cancel"></span>
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</div>
