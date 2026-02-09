import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['toggle', 'menu'];

    connect() {
        // Bind handler to instance for proper removal
        this.boundHandleOutsideClick = this.handleOutsideClick.bind(this);
        document.addEventListener('click', this.boundHandleOutsideClick);
    }

    disconnect() {
        document.removeEventListener('click', this.boundHandleOutsideClick);
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
