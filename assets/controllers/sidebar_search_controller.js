import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['container', 'input', 'item'];

    toggle(event) {
        event.preventDefault();
        const container = this.containerTarget;
        
        container.classList.toggle('active');

        if (container.classList.contains('active')) {
            // Wait for transition to start before focusing
            setTimeout(() => {
                this.inputTarget.focus();
            }, 50);
        }
    }

    search() {
        const query = this.inputTarget.value.toLowerCase();
        
        this.itemTargets.forEach(item => {
            const name = item.dataset.name ? item.dataset.name.toLowerCase() : '';
            const location = item.dataset.location ? item.dataset.location.toLowerCase() : '';
            const text = item.textContent.toLowerCase();
            
            // Search in data attributes if available, otherwise full text
            if (name.includes(query) || location.includes(query) || text.includes(query)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    }
}
