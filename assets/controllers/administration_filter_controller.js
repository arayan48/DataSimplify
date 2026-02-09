import { Controller } from '@hotwired/stimulus';
import * as Turbo from '@hotwired/turbo';

export default class extends Controller {
    static targets = ['search', 'workPackage', 'year', 'partner'];

    connect() {
        console.log('Administration filter controller connected');
        // Restaurer les valeurs des filtres depuis l'URL
        const urlParams = new URLSearchParams(window.location.search);
        
        if (this.hasSearchTarget) {
            this.searchTarget.value = urlParams.get('search') || '';
        }
        if (this.hasWorkPackageTarget) {
            this.workPackageTarget.value = urlParams.get('wp') || '';
        }
        if (this.hasYearTarget) {
            this.yearTarget.value = urlParams.get('year') || '';
        }
        if (this.hasPartnerTarget) {
            this.partnerTarget.value = urlParams.get('partner') || '';
        }
    }

    filter() {
        console.log('Filter method called');
        const params = new URLSearchParams();

        // Ajouter les paramètres de recherche
        if (this.hasSearchTarget && this.searchTarget.value) {
            params.set('search', this.searchTarget.value);
        }

        if (this.hasWorkPackageTarget && this.workPackageTarget.value) {
            params.set('wp', this.workPackageTarget.value);
        }

        if (this.hasYearTarget && this.yearTarget.value) {
            params.set('year', this.yearTarget.value);
        }

        if (this.hasPartnerTarget && this.partnerTarget.value) {
            params.set('partner', this.partnerTarget.value);
        }

        // Rediriger avec les nouveaux paramètres en utilisant Turbo
        const queryString = params.toString();
        const newUrl = queryString ? `${window.location.pathname}?${queryString}` : window.location.pathname;
        
        console.log('Navigating to:', newUrl);
        Turbo.visit(newUrl);
    }

    // Méthode pour gérer la recherche avec un délai (debounce)
    searchDebounce(event) {
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => {
            this.filter();
        }, 500); // Attendre 500ms après la dernière frappe
    }

    reset() {
        // Réinitialiser tous les filtres
        if (this.hasSearchTarget) this.searchTarget.value = '';
        if (this.hasWorkPackageTarget) this.workPackageTarget.value = '';
        if (this.hasYearTarget) this.yearTarget.value = '';
        if (this.hasPartnerTarget) this.partnerTarget.value = '';
        
        // Rediriger vers la page sans paramètres
        Turbo.visit(window.location.pathname);
    }
}
