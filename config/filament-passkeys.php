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
    | Labels
    |--------------------------------------------------------------------------
    */
    'login_button_label' => 'Sign in with passkey',
    'user_menu_item_label' => 'Passkeys',
];
