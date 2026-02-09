import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['searchInput', 'partenaireFilter', 'yearFilter', 'wpFilter', 'tableBody', 'pagination', 'totalCount', 'loader'];
    static values = {
        url: String,
        currentPage: { type: Number, default: 1 },
        totalPages: { type: Number, default: 1 }
    };

    connect() {
        this.loadEntreprises();
    }

    search() {
        this.currentPageValue = 1;
        this.loadEntreprises();
    }

    filterByPartenaire() {
        this.currentPageValue = 1;
        this.loadEntreprises();
    }

    filterByYear() {
        this.currentPageValue = 1;
        this.loadEntreprises();
    }

    filterByWp() {
        this.currentPageValue = 1;
        this.loadEntreprises();
    }

    applyFilters() {
        this.currentPageValue = 1;
        this.loadEntreprises();
    }

    resetFilters() {
        if (this.hasPartenaireFilterTarget) this.partenaireFilterTarget.value = '';
        if (this.hasYearFilterTarget) this.yearFilterTarget.value = '';
        if (this.hasWpFilterTarget) this.wpFilterTarget.value = '';
        this.currentPageValue = 1;
        this.loadEntreprises();
    }

    goToPage(event) {
        event.preventDefault();
        const page = parseInt(event.currentTarget.dataset.page);
        if (page > 0 && page <= this.totalPagesValue) {
            this.currentPageValue = page;
            this.loadEntreprises();
        }
    }

    previousPage(event) {
        event.preventDefault();
        if (this.currentPageValue > 1) {
            this.currentPageValue--;
            this.loadEntreprises();
        }
    }

    nextPage(event) {
        event.preventDefault();
        if (this.currentPageValue < this.totalPagesValue) {
            this.currentPageValue++;
            this.loadEntreprises();
        }
    }

    async loadEntreprises() {
        // Afficher le loader
        if (this.hasLoaderTarget) {
            this.loaderTarget.classList.remove('hidden');
        }

        const searchValue = this.hasSearchInputTarget ? this.searchInputTarget.value : '';
        const partenaireValue = this.hasPartenaireFilterTarget ? this.partenaireFilterTarget.value : '';
        const yearValue = this.hasYearFilterTarget ? this.yearFilterTarget.value : '';
        const wpValue = this.hasWpFilterTarget ? this.wpFilterTarget.value : '';

        const params = new URLSearchParams({
            page: this.currentPageValue,
            search: searchValue,
            partenaire: partenaireValue,
            year: yearValue,
            wp: wpValue
        });

        console.log('Loading URL:', `${this.urlValue}?${params.toString()}`);

        try {
            const response = await fetch(`${this.urlValue}?${params.toString()}`);
            console.log('Response status:', response.status);
            console.log('Response OK:', response.ok);
            
            const result = await response.json();
            console.log('API Response:', result);

            if (result.success) {
                this.renderTable(result.data);
                this.renderPagination(result.pagination);
                this.totalPagesValue = result.pagination.pages;
                
                if (this.hasTotalCountTarget) {
                    this.totalCountTarget.textContent = result.pagination.total;
                }
            } else {
                console.error('API returned success=false:', result);
                this.showError();
            }
        } catch (error) {
            console.error('Erreur lors du chargement des entreprises:', error);
            this.showError();
        } finally {
            // Cacher le loader
            if (this.hasLoaderTarget) {
                this.loaderTarget.classList.add('hidden');
            }
        }
    }

    renderTable(entreprises) {
        if (entreprises.length === 0) {
            this.tableBodyTarget.innerHTML = `
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                        Aucune entreprise trouvée
                    </td>
                </tr>
            `;
            return;
        }

        this.tableBodyTarget.innerHTML = entreprises.map(entreprise => `
            <tr class="border-b border-gray-200 hover:bg-gray-50">
                <th scope="row" class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">
                    ${this.escapeHtml(entreprise.nom)}
                </th>
                <td class="px-4 py-3">${this.escapeHtml(entreprise.secteur)}</td>
                <td class="px-4 py-3">${this.escapeHtml(entreprise.partenaire)}</td>
                <td class="px-4 py-3">${this.escapeHtml(entreprise.ville)}</td>
                <td class="px-4 py-3">${this.escapeHtml(entreprise.taille)}</td>
                <td class="px-4 py-3">${this.escapeHtml(entreprise.createdAt)}</td>
                <td class="px-4 py-3">
                    <div class="flex items-center justify-end">
                        <button 
                            data-dropdown-toggle="dropdown-${entreprise.id}" 
                            class="inline-flex items-center p-1.5 text-sm font-medium text-center text-gray-500 hover:text-gray-800 hover:bg-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-200 transition-colors" 
                            type="button">
                            <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                            </svg>
                        </button>
                        <div id="dropdown-${entreprise.id}" class="hidden z-10 w-48 bg-white rounded divide-y divide-gray-100 shadow">
                            <ul class="py-1 text-sm text-gray-700">
                                <li>
                                    <a href="/administration/entreprise/${entreprise.id}" class="flex items-center px-4 py-2.5 hover:bg-gray-100">
                                        <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Voir
                                    </a>
                                </li>
                                <li>
                                    <a href="/administration/entreprise/${entreprise.id}/modifier" class="flex items-center px-4 py-2.5 hover:bg-gray-100">
                                        <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Modifier
                                    </a>
                                </li>
                            </ul>
                            <div class="py-1">
                                <a href="#" class="flex items-center px-4 py-2.5 text-red-600 hover:bg-red-50">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Supprimer
                                </a>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        `).join('');

        // Réinitialiser Flowbite pour les nouveaux dropdowns
        this.initDropdowns();
    }

    initDropdowns() {
        // Attendre que le DOM soit mis à jour puis réinitialiser Flowbite
        setTimeout(() => {
            if (window.initFlowbite) {
                window.initFlowbite();
            }
        }, 50);
    }

    renderPagination(pagination) {
        const { current, pages } = pagination;
        
        if (pages <= 1) {
            this.paginationTarget.innerHTML = '';
            return;
        }

        let paginationHtml = `
            <ul class="inline-flex items-stretch -space-x-px">
                <li>
                    <a href="#" 
                       data-action="click->entreprises-table#previousPage" 
                       class="flex items-center justify-center h-full py-1.5 px-3 ml-0 text-gray-500 bg-white rounded-l-lg border border-gray-300 hover:bg-gray-100 hover:text-gray-700 ${current === 1 ? 'opacity-50 cursor-not-allowed' : ''}">
                        <span class="sr-only">Previous</span>
                        <svg class="w-5 h-5" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </li>
        `;

        // Génération des numéros de page
        const maxButtons = 7;
        let startPage = Math.max(1, current - Math.floor(maxButtons / 2));
        let endPage = Math.min(pages, startPage + maxButtons - 1);

        if (endPage - startPage < maxButtons - 1) {
            startPage = Math.max(1, endPage - maxButtons + 1);
        }

        if (startPage > 1) {
            paginationHtml += this.createPageButton(1, current);
            if (startPage > 2) {
                paginationHtml += `<li><span class="flex items-center justify-center text-sm py-2 px-3 leading-tight text-gray-500 bg-white border border-gray-300">...</span></li>`;
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            paginationHtml += this.createPageButton(i, current);
        }

        if (endPage < pages) {
            if (endPage < pages - 1) {
                paginationHtml += `<li><span class="flex items-center justify-center text-sm py-2 px-3 leading-tight text-gray-500 bg-white border border-gray-300">...</span></li>`;
            }
            paginationHtml += this.createPageButton(pages, current);
        }

        paginationHtml += `
                <li>
                    <a href="#" 
                       data-action="click->entreprises-table#nextPage" 
                       class="flex items-center justify-center h-full py-1.5 px-3 leading-tight text-gray-500 bg-white rounded-r-lg border border-gray-300 hover:bg-gray-100 hover:text-gray-700 ${current === pages ? 'opacity-50 cursor-not-allowed' : ''}">
                        <span class="sr-only">Next</span>
                        <svg class="w-5 h-5" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </li>
            </ul>
        `;

        this.paginationTarget.innerHTML = paginationHtml;
    }

    createPageButton(page, currentPage) {
        const isActive = page === currentPage;
        const classes = isActive
            ? 'flex items-center justify-center text-sm z-10 py-2 px-3 leading-tight text-primary-600 bg-primary-50 border border-primary-300 hover:bg-primary-100 hover:text-primary-700'
            : 'flex items-center justify-center text-sm py-2 px-3 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700';

        return `
            <li>
                <a href="#" 
                   data-action="click->entreprises-table#goToPage" 
                   data-page="${page}" 
                   class="${classes}"
                   ${isActive ? 'aria-current="page"' : ''}>
                    ${page}
                </a>
            </li>
        `;
    }

    showError() {
        this.tableBodyTarget.innerHTML = `
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-red-500 dark:text-red-400">
                    Une erreur s'est produite lors du chargement des données
                </td>
            </tr>
        `;
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}
