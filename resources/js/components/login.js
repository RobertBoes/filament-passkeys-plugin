import { Passkeys, UnexpectedResponseError, UserCancelledError } from '@laravel/passkeys';

export default function filamentPasskeysLogin({ labels = {} } = {}) {
    return {
        labels: {
            signingIn: 'Signing in…',
            signIn: 'Sign in with passkey',
            failure: 'Passkey sign in failed.',
            ...labels,
        },
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
                const result = await Passkeys.verify({
                    remember: () => this.shouldRemember(),
                });
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
                // The login route is guest-only, so a redirect means the user
                // is already signed in (e.g. in another tab). Reloading lets
                // Filament send them on to the panel.
                if (e instanceof UnexpectedResponseError && e.redirected) {
                    window.location.reload();
                    return;
                }
                if (e instanceof UnexpectedResponseError) {
                    this.error = this.labels.failure;
                    return;
                }
                this.error = e?.message ?? this.labels.failure;
            } finally {
                this.loading = false;
            }
        },
        // Mirrors the "Remember me" checkbox on Filament's login form. The
        // button can be moved to a render hook outside the form, where there
        // is no Livewire component and $wire throws.
        shouldRemember() {
            try {
                return Boolean(this.$wire?.data?.remember);
            } catch {
                return false;
            }
        },
    };
}
