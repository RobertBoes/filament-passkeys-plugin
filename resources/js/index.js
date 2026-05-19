import filamentPasskeysLogin from './components/login.js';
import filamentPasskeysManager from './components/manager.js';

const register = (Alpine) => {
    Alpine.data('filamentPasskeysLogin', filamentPasskeysLogin);
    Alpine.data('filamentPasskeysManager', filamentPasskeysManager);
};

if (window.Alpine) {
    register(window.Alpine);
} else {
    document.addEventListener('alpine:init', () => register(window.Alpine));
}
