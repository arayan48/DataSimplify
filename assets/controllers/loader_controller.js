import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.loader = document.getElementById('global-loader');
    }

    show() {
        if (this.loader) {
            this.loader.style.display = 'flex';
        }
    }

    hide() {
        if (this.loader) {
            this.loader.style.display = 'none';
        }
    }

    // Méthode helper pour wraper les fetch avec loader
    async fetchWithLoader(url, options = {}) {
        this.show();
        try {
            const response = await fetch(url, options);
            return response;
        } finally {
            this.hide();
        }
    }
}

// Export global functions pour utiliser le loader partout
window.showLoader = function() {
    const loader = document.getElementById('global-loader');
    if (loader) {
        loader.style.display = 'flex';
    }
};

window.hideLoader = function() {
    const loader = document.getElementById('global-loader');
    if (loader) {
        loader.style.display = 'none';
    }
};
