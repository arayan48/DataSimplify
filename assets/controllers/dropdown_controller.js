import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['toggle', 'menu'];

    connect() {
        // Fermer le dropdown quand on clique en dehors
        document.addEventListener('click', (e) => this.handleOutsideClick(e));
    }

    disconnect() {
        document.removeEventListener('click', (e) => this.handleOutsideClick(e));
    }

    toggle() {
        this.menuTarget.classList.toggle('show');
        this.element.classList.toggle('active');
    }

    handleOutsideClick(e) {
        if (!this.element.contains(e.target)) {
            this.menuTarget.classList.remove('show');
            this.element.classList.remove('active');
        }
    }
}
