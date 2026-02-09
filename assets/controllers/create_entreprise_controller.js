import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["categoryCheckbox", "conditionalSection", "countrySelector", "countryInput", "countryDropdown", "countrySearch", "countryList"];

    connect() {
        console.log('CreateEntreprise loaded');
        this.setupAccordionListeners();
        this.initializeAccordionStates();
        this.setupCategoryCheckboxes();
        this.setupCountrySelector();
        
        // Fermer le dropdown quand on clique ailleurs
        document.addEventListener('click', (e) => {
            if (this.hasCountrySelectorTarget) {
                if (!this.countrySelectorTarget.contains(e.target)) {
                    this.closeCountryDropdown();
                }
            }
        });

        // Repositionner le dropdown lors du scroll
        window.addEventListener('scroll', () => {
            this.updateCountryDropdownPosition();
        }, true);

        // Repositionner le dropdown lors du resize
        window.addEventListener('resize', () => {
            this.updateCountryDropdownPosition();
        });
    }

    initializeAccordionStates() {
        const items = this.element.querySelectorAll('.accordion-item:not(.conditional-section)');
        items.forEach((item, index) => {
            const header = item.querySelector('.accordion-header');
            if (header) {
                header.setAttribute('data-section', index);
                if (index === 0) {
                    header.classList.add('active');
                    header.nextElementSibling?.classList.add('active');
                }
            }
        });
    }

    setupAccordionListeners() {
        const headers = this.element.querySelectorAll('.accordion-header');
        headers.forEach(header => {
            header.addEventListener('click', (e) => {
                this.toggleAccordion(header);
            });
        });
    }

    toggleAccordion(header) {
        const content = header.nextElementSibling;
        if (!content) return;
        
        const isActive = header.classList.contains('active');
        
        if (isActive) {
            header.classList.remove('active');
            content.classList.remove('active');
        } else {
            header.classList.add('active');
            content.classList.add('active');
        }
        
        // Scroll vers la section si elle s'ouvre
        if (!isActive) {
            setTimeout(() => {
                header.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        }
    }

    setupCategoryCheckboxes() {
        this.categoryCheckboxTargets.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                this.toggleConditionalSections();
            });
        });
    }

    toggleConditionalSections() {
        const checkedCategories = this.categoryCheckboxTargets
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        this.conditionalSectionTargets.forEach(section => {
            const category = section.dataset.category;
            
            if (checkedCategories.includes(category)) {
                section.style.display = '';
            } else {
                section.style.display = 'none';
                // Fermer l'accordéon s'il était ouvert
                const header = section.querySelector('.accordion-header');
                const content = section.querySelector('.accordion-content');
                if (header && content) {
                    header.classList.remove('active');
                    content.classList.remove('active');
                }
            }
        });
    }

    setupCountrySelector() {
        if (!this.hasCountryListTarget) return;
        
        const countryItems = this.countryListTarget.querySelectorAll('.country-item');
        countryItems.forEach(item => {
            item.addEventListener('click', () => {
                this.selectCountry(item.dataset.country);
            });
        });
    }

    toggleCountryDropdown(event) {
        event.stopPropagation();
        const isVisible = this.countryDropdownTarget.style.display === 'flex';
        
        if (isVisible) {
            this.closeCountryDropdown();
        } else {
            this.openCountryDropdown();
        }
    }

    openCountryDropdown() {
        this.countryDropdownTarget.style.display = 'flex';
        this.updateCountryDropdownPosition();
        
        if (this.hasCountrySearchTarget) {
            this.countrySearchTarget.value = '';
            setTimeout(() => {
                this.countrySearchTarget.focus();
            }, 50);
            this.filterCountries();
        }
    }

    updateCountryDropdownPosition() {
        if (!this.hasCountryDropdownTarget || this.countryDropdownTarget.style.display !== 'flex') {
            return;
        }

        // Positionner le dropdown par rapport à l'input
        const inputRect = this.countryInputTarget.getBoundingClientRect();
        this.countryDropdownTarget.style.top = `${inputRect.bottom + 4}px`;
        this.countryDropdownTarget.style.left = `${inputRect.left}px`;
        this.countryDropdownTarget.style.width = `${inputRect.width}px`;
    }

    closeCountryDropdown() {
        if (this.hasCountryDropdownTarget) {
            this.countryDropdownTarget.style.display = 'none';
        }
    }

    selectCountry(countryName) {
        this.countryInputTarget.value = countryName;
        this.closeCountryDropdown();
    }

    filterCountries() {
        const searchTerm = this.countrySearchTarget.value.toLowerCase();
        const categories = this.countryListTarget.querySelectorAll('.country-category');
        
        categories.forEach(category => {
            const items = category.querySelectorAll('.country-item');
            let hasVisibleItems = false;
            
            items.forEach(item => {
                const countryName = item.dataset.country.toLowerCase();
                if (countryName.includes(searchTerm)) {
                    item.style.display = '';
                    hasVisibleItems = true;
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Afficher/masquer la catégorie selon si elle a des items visibles
            if (hasVisibleItems) {
                category.style.display = '';
            } else {
                category.style.display = 'none';
            }
        });
    }
}
