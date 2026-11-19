@props([
    'label' => null,
])

@php
    // Vertical rhythm is applied via margins rather than a flex `gap`: Alpine's
    // x-show strips the inline `display` property when it reveals the element,
    // dropping it back to `display: block` where `gap` has no effect.
    $blockGap = 'calc(var(--spacing) * 6)';
    $dividerGap = 'calc(var(--spacing) * 6)';
    $labelGap = 'calc(var(--spacing) * 2)';
    $label ??= __('filament-passkeys::passkeys.login.button');
    $orLabel = __('filament-passkeys::passkeys.login.or');
@endphp

<div
    x-data="filamentPasskeysLogin({
        labels: {
            signingIn: @js(__('filament-passkeys::passkeys.login.signing_in')),
            signIn: @js($label),
            failure: @js(__('filament-passkeys::passkeys.login.failure')),
        },
    })"
    x-cloak
    x-show="supported"
    class="fi-filament-passkeys-login"
    style="margin-top: {{ $blockGap }};"
>
    <div
        role="separator"
        aria-label="{{ $orLabel }}"
        style="display: flex; align-items: center; gap: {{ $labelGap }}; font-size: var(--text-xs); text-transform: uppercase; letter-spacing: 0.05em;"
    >
        <span style="flex: 1; height: 1px; background-color: var(--color-gray-200);"></span>
        <span style="color: var(--color-gray-500);">{{ $orLabel }}</span>
        <span style="flex: 1; height: 1px; background-color: var(--color-gray-200);"></span>
    </div>

    <x-filament::button
        tag="button"
        type="button"
        color="gray"
        outlined
        icon="heroicon-o-key"
        x-on:click="signIn"
        x-bind:disabled="loading"
        style="width: 100%; margin-top: {{ $dividerGap }};"
    >
        <span x-text="loading ? labels.signingIn : labels.signIn"></span>
    </x-filament::button>

    <template x-if="error">
        <div class="fi-callout fi-color fi-color-danger" role="alert" style="margin-top: {{ $dividerGap }};">
            <x-filament::icon icon="heroicon-o-x-circle" class="fi-callout-icon" />
            <div class="fi-callout-main">
                <div class="fi-callout-text">
                    <p class="fi-callout-description" x-text="error"></p>
                </div>
            </div>
        </div>
    </template>
</div>
