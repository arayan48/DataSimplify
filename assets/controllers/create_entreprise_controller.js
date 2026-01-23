import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        console.log('CreateEntreprise loaded');
        this.requiredFields = {
            0: ['nom', 'siret', 'secteur', 'ville', 'codepostal', 'pays', 'adresse'],
            1: ['telephone', 'email'],
            2: ['prenom_representant', 'nom_representant', 'poste_representant', 'email_representant']
        };
        
        this.setupAccordionListeners();
        this.initializeAccordionStates();
        this.setupFormTracking();
        this.setupProgressBar();
    }

    initializeAccordionStates() {
        const items = this.element.querySelectorAll('.accordion-item');
        items.forEach((item, index) => {
            const header = item.querySelector('.accordion-header');
            if (header) {
                header.setAttribute('data-section', index);
                if (index === 0) {
                    header.classList.add('active');
                    header.nextElementSibling?.classList.add('active');
                } else {
                    header.classList.add('disabled');
                }
            }
        });
    }

    setupAccordionListeners() {
        const headers = this.element.querySelectorAll('.accordion-header');
        headers.forEach(header => {
            header.addEventListener('click', (e) => {
                if (!header.classList.contains('disabled')) {
                    this.toggleAccordion(header);
                }
            });
        });
    }

    toggleAccordion(header) {
        const content = header.nextElementSibling;
        if (!content) return;
        
        // Fermer tous les autres accordéons
        this.element.querySelectorAll('.accordion-header').forEach(h => {
            if (h !== header && h.classList.contains('active')) {
                h.classList.remove('active');
                const nextEl = h.nextElementSibling;
                if (nextEl) nextEl.classList.remove('active');
            }
        });
        
        // Ouvrir le nouvel accordéon
        header.classList.add('active');
        content.classList.add('active');
        
        // Scroll vers la section
        setTimeout(() => {
            header.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
    }

    setupFormTracking() {
        const form = this.element.querySelector('.create-entreprise-form');
        if (!form) return;
        
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('change', () => {
                this.updateProgressBar();
            });
        });
    }

    setupProgressBar() {
        const container = this.element;
        const existingBar = container.querySelector('.progress-container');
        if (existingBar) {
            existingBar.remove();
        }
        
        const progressContainer = document.createElement('div');
        progressContainer.className = 'progress-container';
        progressContainer.innerHTML = `
            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>
            <div class="progress-text">
                <span class="progress-current">0</span>/<span class="progress-total">3</span> sections complétées
            </div>
        `;
        
        const header = container.querySelector('.create-entreprise-header');
        if (header && header.nextElementSibling) {
            header.parentNode.insertBefore(progressContainer, header.nextElementSibling);
        }
        
        this.updateProgressBar();
    }

    validateSection(sectionIndex) {
        const requiredFields = this.requiredFields[sectionIndex] || [];
        if (requiredFields.length === 0) return true;
        
        const form = this.element.querySelector('.create-entreprise-form');
        if (!form) return false;
        
        let allFilled = true;
        requiredFields.forEach(fieldName => {
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (!field || !field.value.trim()) {
                allFilled = false;
            }
        });
        
        return allFilled;
    }

    updateProgressBar() {
        const progressFill = this.element.querySelector('.progress-fill');
        const progressCurrent = this.element.querySelector('.progress-current');
        const items = this.element.querySelectorAll('.accordion-item');
        
        let completedSections = 0;
        let totalSections = items.length;
        
        items.forEach((item, index) => {
            const header = item.querySelector('.accordion-header');
            if (!header) return;
            
            if (this.validateSection(index)) {
                completedSections++;
                header.classList.add('completed');
                this.unlockNextSection(index);
            } else {
                header.classList.remove('completed');
            }
        });
        
        const progressPercent = totalSections > 0 ? (completedSections / totalSections) * 100 : 0;
        if (progressFill) {
            progressFill.style.width = progressPercent + '%';
        }
        if (progressCurrent) {
            progressCurrent.textContent = completedSections;
        }
    }

    unlockNextSection(currentIndex) {
        const nextIndex = currentIndex + 1;
        const items = this.element.querySelectorAll('.accordion-item');
        
        if (nextIndex < items.length) {
            const nextHeader = items[nextIndex].querySelector('.accordion-header');
            if (nextHeader && this.validateSection(currentIndex)) {
                nextHeader.classList.remove('disabled');
            }
        }
    }
}
