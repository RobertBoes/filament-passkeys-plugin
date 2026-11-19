<?php

declare(strict_types=1);

namespace RobertBoes\FilamentPasskeys\Support;

use Laravel\Passkeys\Passkey;

class AuthenticatorIcons
{
    /**
     * Resolve the authenticator's icons as data URIs. When only one variant
     * exists it is used for both themes, so the icon does not vanish when the
     * theme switches.
     *
     * @return array{authenticator: string|null, iconLight: string|null, iconDark: string|null}
     */
    public static function for(Passkey $passkey): array
    {
        $light = $passkey->authenticator_icon_light;
        $dark = $passkey->authenticator_icon_dark;

        return [
            'authenticator' => $passkey->authenticator,
            'iconLight' => $light ?? $dark,
            'iconDark' => $dark ?? $light,
        ];
    }
}
