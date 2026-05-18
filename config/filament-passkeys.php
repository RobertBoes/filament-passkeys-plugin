<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Render hook
    |--------------------------------------------------------------------------
    |
    | Where the "Sign in with passkey" button is injected on the Filament
    | login page. See \Filament\View\PanelsRenderHook for available hooks.
    |
    */
    'login_render_hook' => \Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,

    /*
    |--------------------------------------------------------------------------
    | Client script source
    |--------------------------------------------------------------------------
    |
    | The plugin loads the @laravel/passkeys client from a CDN by default so it
    | works without a bundler. If you bundle the package yourself, set this to
    | null and expose `window.Passkeys` from your app's JS.
    |
    */
    'client_script_src' => 'https://esm.sh/@laravel/passkeys@latest',

    /*
    |--------------------------------------------------------------------------
    | Labels
    |--------------------------------------------------------------------------
    */
    'login_button_label' => 'Sign in with passkey',
    'user_menu_item_label' => 'Passkeys',
];
