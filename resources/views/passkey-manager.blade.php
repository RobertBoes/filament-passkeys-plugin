@props([
    'passkeys' => [],
])

@php
    $blockGap = 'calc(var(--spacing) * 6)';
    $itemGap = 'calc(var(--spacing) * 2)';

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
        labels: {
            adding: @js(__('Adding…')),
            add: @js(__('Add passkey')),
            added: @js(__('Passkey added.')),
            exists: @js(__('That device already has a passkey for this account.')),
            failure: @js(__('Could not add passkey.')),
            notSupported: @js(__('Your browser does not support passkeys. Try a recent version of Chrome, Safari, Edge, or Firefox.')),
            notSupportedHeading: @js(__('Passkeys not supported')),
            invalidDomain: @js(__('Passkeys cannot be used on this domain. Make sure you are on the same origin as the app.')),
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
                    <form
                        method="POST"
                        x-bind:action="`/user/passkeys/${passkey.id}`"
                        onsubmit="return confirm(@js(__('Remove this passkey?')))"
                    >
                        @csrf
                        @method('DELETE')
                        <x-filament::button
                            tag="button"
                            type="submit"
                            color="danger"
                            size="sm"
                            outlined
                        >
                            {{ __('Remove') }}
                        </x-filament::button>
                    </form>
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
                x-on:click="addPasskey"
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
</div>
