import {
    Passkeys,
    PasskeyExistsError,
    UserCancelledError,
    NotSupportedError,
    InvalidDomainError,
} from '@laravel/passkeys';

export default function filamentPasskeysManager({
    labels = {},
    initialPasskeys = [],
    confirmPasswordUrl = '',
    confirmPasswordStatusUrl = '',
    deletePasskeyBaseUrl = '',
    csrfToken = '',
} = {}) {
    return {
        labels: {
            adding: 'Adding…',
            add: 'Add passkey',
            added: 'Passkey added.',
            exists: 'That device already has a passkey for this account.',
            failure: 'Could not add passkey.',
            confirmHeading: 'Confirm your password',
            confirmDescription: 'For your security, please confirm your password to continue.',
            passwordLabel: 'Password',
            confirmAction: 'Confirm',
            cancel: 'Cancel',
            invalidPassword: 'The password is incorrect.',
            networkError: 'Could not verify password. Please try again.',
            removeHeading: 'Remove passkey?',
            removeDescription: 'You will no longer be able to sign in with this passkey.',
            removeAction: 'Remove',
            removeFailure: 'Could not remove passkey.',
            removed: 'Passkey removed.',
            addedJustNow: 'Added just now',
            notSupported: 'Your browser does not support passkeys. Try a recent version of Chrome, Safari, Edge, or Firefox.',
            notSupportedHeading: 'Passkeys not supported',
            invalidDomain: 'Passkeys cannot be used on this domain. Make sure you are on the same origin as the app.',
            ...labels,
        },
        confirmPasswordUrl,
        confirmPasswordStatusUrl,
        deletePasskeyBaseUrl,
        csrfToken,
        modalId: 'filament-passkeys-confirm-password',
        deleteModalId: 'filament-passkeys-confirm-delete',
        supported: true,
        passkeys: [...initialPasskeys],
        name: '',
        password: '',
        passwordError: null,
        confirming: false,
        loading: false,
        error: null,
        success: null,
        passkeyToDelete: null,
        pendingAction: null,
        init() {
            this.supported = Passkeys.isSupported();
        },
        async promptPassword() {
            if (! this.supported || ! this.name || this.loading) {
                return;
            }
            this.error = null;
            this.success = null;
            await this.ensurePasswordConfirmed(() => this.addPasskey());
        },
        confirmDelete(passkey) {
            this.passkeyToDelete = passkey;
            this.error = null;
            this.success = null;
            this.$dispatch('open-modal', { id: this.deleteModalId });
        },
        async beginDelete() {
            const passkey = this.passkeyToDelete;
            this.$dispatch('close-modal', { id: this.deleteModalId });
            if (! passkey) {
                return;
            }
            await this.ensurePasswordConfirmed(() => this.deletePasskey(passkey));
        },
        async ensurePasswordConfirmed(next) {
            if (await this.isPasswordConfirmed()) {
                await next();
                return;
            }
            this.password = '';
            this.passwordError = null;
            this.pendingAction = next;
            this.$dispatch('open-modal', { id: this.modalId });
        },
        async isPasswordConfirmed() {
            if (! this.confirmPasswordStatusUrl) {
                return false;
            }
            try {
                const response = await fetch(this.confirmPasswordStatusUrl, {
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' },
                });
                if (! response.ok) {
                    return false;
                }
                const data = await response.json();
                return data?.confirmed === true;
            } catch {
                return false;
            }
        },
        async confirmPassword() {
            if (this.confirming || ! this.password) {
                return;
            }
            this.confirming = true;
            this.passwordError = null;
            try {
                const response = await fetch(this.confirmPasswordUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify({ password: this.password }),
                });

                if (response.status === 204) {
                    this.password = '';
                    this.$dispatch('close-modal', { id: this.modalId });
                    const next = this.pendingAction;
                    this.pendingAction = null;
                    if (next) {
                        await next();
                    }
                    return;
                }

                if (response.status === 422) {
                    const data = await response.json().catch(() => ({}));
                    this.passwordError = data?.errors?.password?.[0] ?? this.labels.invalidPassword;
                    return;
                }

                this.passwordError = this.labels.networkError;
            } catch (e) {
                this.passwordError = e?.message ?? this.labels.networkError;
            } finally {
                this.confirming = false;
            }
        },
        async addPasskey() {
            this.loading = true;
            this.error = null;
            this.success = null;
            try {
                const result = await Passkeys.register({ name: this.name });
                this.passkeys.unshift({
                    id: result?.id,
                    name: result?.name ?? this.name,
                    addedLabel: this.labels.addedJustNow,
                });
                this.success = this.labels.added;
                this.name = '';
            } catch (e) {
                if (e instanceof UserCancelledError) {
                    return;
                }
                if (e instanceof PasskeyExistsError) {
                    this.error = this.labels.exists;
                    return;
                }
                if (e instanceof NotSupportedError) {
                    this.error = this.labels.notSupported;
                    return;
                }
                if (e instanceof InvalidDomainError) {
                    this.error = this.labels.invalidDomain;
                    return;
                }
                this.error = e?.message ?? this.labels.failure;
            } finally {
                this.loading = false;
            }
        },
        async deletePasskey(passkey) {
            if (! passkey || ! this.deletePasskeyBaseUrl) {
                return;
            }
            this.loading = true;
            this.error = null;
            this.success = null;
            try {
                const response = await fetch(`${this.deletePasskeyBaseUrl}/${passkey.id}`, {
                    method: 'DELETE',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                });
                if (response.ok || response.status === 204) {
                    this.passkeys = this.passkeys.filter((p) => String(p.id) !== String(passkey.id));
                    this.success = this.labels.removed;
                    return;
                }
                this.error = this.labels.removeFailure;
            } catch (e) {
                this.error = e?.message ?? this.labels.removeFailure;
            } finally {
                this.loading = false;
                this.passkeyToDelete = null;
            }
        },
    };
}
