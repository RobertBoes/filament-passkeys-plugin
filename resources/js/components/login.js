import { Passkeys, UserCancelledError } from '@laravel/passkeys';

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
                this.error = e?.message ?? this.labels.failure;
            } finally {
                this.loading = false;
            }
        },
    };
}
