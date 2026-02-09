import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'modal', 'modalTitle', 'form', 'partenaireId', 
        'nomInput', 'telephoneInput', 'emailInput', 'adresseInput',
        'villeInput', 'codePostalInput', 'siteWebInput', 'descriptionInput',
        'checkbox', 'checkAll', 'deleteBtn',
        'usersSection', 'usersList', 'searchInput',
        'addDropdown', 'addUsersList', 'addSearchInput'
    ];

    static values = {
        addPartner: String,
        editPartner: String,
        confirmDelete: String,
        error: String,
        selectUser: String,
        usersAdded: String,
        userRemoved: String
    };

    connect() {
        console.log('Partenaires controller loaded');
        this.currentPartenaireId = null;
        this.allUsers = [];
        this.allAvailableUsers = [];
        this.selectedUserIds = new Set();
        
        // Fermer les dropdowns si on clique en dehors
        document.addEventListener('click', this.handleClickOutside.bind(this));
    }

    // Helper pour obtenir l'URL avec la locale
    getLocalizedUrl(path) {
        const locale = document.documentElement.lang || 'fr';
        const url = `/${locale}${path}`;
        console.log('getLocalizedUrl (partenaires):', path, '->', url, 'locale:', locale);
        return url;
    }

    disconnect() {
        document.removeEventListener('click', this.handleClickOutside.bind(this));
    }

    handleClickOutside(event) {
        if (!event.target.closest('.add-user-box')) {
            if (this.hasAddDropdownTarget) {
                this.addDropdownTarget.style.display = 'none';
            }
        }
    }

    // Empêcher le scroll de la page quand on scroll dans le modal
    preventBodyScroll() {
        document.body.style.overflow = 'hidden';
        
        // Empêcher le scroll sur le modal-left
        const modalLeft = document.querySelector('.modal-left');
        if (modalLeft) {
            modalLeft.addEventListener('wheel', (e) => {
                e.stopPropagation();
            }, { passive: true });
        }
        
        // Empêcher le scroll sur le modal-right
        const modalRight = document.querySelector('.modal-right');
        if (modalRight) {
            modalRight.addEventListener('wheel', (e) => {
                e.stopPropagation();
            }, { passive: true });
        }
    }

    enableBodyScroll() {
        document.body.style.overflow = '';
    }

    // Sélection
    toggleAll(event) {
        const isChecked = event.target.checked;
        this.checkboxTargets.forEach(checkbox => {
            checkbox.checked = isChecked;
        });
        this.updateDeleteBtn();
    }

    updateDeleteBtn() {
        const selectedCount = this.checkboxTargets.filter(cb => cb.checked).length;
        this.deleteBtnTarget.disabled = selectedCount === 0;
    }

    // Modal
    openCreateModal() {
        this.modalTitleTarget.textContent = this.addPartnerValue;
        this.partenaireIdTarget.value = '';
        this.nomInputTarget.value = '';
        this.telephoneInputTarget.value = '';
        this.emailInputTarget.value = '';
        this.adresseInputTarget.value = '';
        this.villeInputTarget.value = '';
        this.codePostalInputTarget.value = '';
        this.siteWebInputTarget.value = '';
        this.descriptionInputTarget.value = '';
        
        // Afficher la section utilisateurs (vide pour la création)
        this.usersSectionTarget.style.display = 'flex';
        this.allUsers = [];
        this.allAvailableUsers = [];
        this.displayUsers([]);
        
        // Charger tous les utilisateurs disponibles
        this.loadAvailableUsers();

        this.modalTarget.classList.add('active');
        this.preventBodyScroll();
    }

    async openEditModal(event) {
        const btn = event.currentTarget;
        
        this.modalTitleTarget.textContent = this.editPartnerValue;
        this.currentPartenaireId = btn.dataset.partenaireId;
        this.partenaireIdTarget.value = this.currentPartenaireId;
        this.nomInputTarget.value = btn.dataset.partenaireNom || '';
        this.telephoneInputTarget.value = btn.dataset.partenaireTelephone || '';
        this.emailInputTarget.value = btn.dataset.partenaireEmail || '';
        this.adresseInputTarget.value = btn.dataset.partenaireAdresse || '';
        this.villeInputTarget.value = btn.dataset.partenaireVille || '';
        this.codePostalInputTarget.value = btn.dataset.partenaireCodePostal || '';
        this.siteWebInputTarget.value = btn.dataset.partenaireSiteWeb || '';
        this.descriptionInputTarget.value = btn.dataset.partenaireDescription || '';

        // Afficher la section utilisateurs pour la modification
        this.usersSectionTarget.style.display = 'flex';
        
        // Charger les utilisateurs du partenaire
        await this.loadUsersForPartenaire(this.currentPartenaireId);

        this.modalTarget.classList.add('active');
        this.preventBodyScroll();
    }

    closeModal() {
        this.modalTarget.classList.remove('active');
        this.formTarget.reset();
        this.currentPartenaireId = null;
        if (this.hasAddDropdownTarget) {
            this.addDropdownTarget.style.display = 'none';
        }
        this.enableBodyScroll();
    }

    // Charger les utilisateurs pour le panneau droit
    async loadUsersForPartenaire(partenaireId) {
        this.usersListTarget.innerHTML = '<div class="loading">Chargement...</div>';
        
        try {
            const response = await fetch(this.getLocalizedUrl(`/administrateur/partenaires/${partenaireId}/users`));
            const data = await response.json();
            this.allUsers = data.users;
            this.displayUsers(data.users);
            
            // Charger aussi tous les utilisateurs disponibles pour l'ajout
            await this.loadAvailableUsers();
        } catch (error) {
            this.usersListTarget.innerHTML = '<div class="error-state">Erreur de chargement</div>';
            console.error('Erreur:', error);
        }
    }

    // Charger tous les utilisateurs disponibles
    async loadAvailableUsers() {
        try {
            const response = await fetch(this.getLocalizedUrl('/administrateur/users/all'));
            const data = await response.json();
            this.allAvailableUsers = data.users || [];
        } catch (error) {
            console.error('Erreur chargement utilisateurs:', error);
            this.allAvailableUsers = [];
        }
    }

    displayUsers(users) {
        if (users.length === 0) {
            this.usersListTarget.innerHTML = '<div class="empty-state">Aucun utilisateur</div>';
        } else {
            const usersHtml = users.map(user => `
                <div class="user-card">
                    <div class="user-card-content">
                        <small>${user.prenom} ${user.nom}</small>
                        <small>${user.email}</small>
                    </div>
                    <button class="btn-remove" 
                            data-action="click->partenaires#removeUser"
                            data-user-id="${user.id}"
                            title="Supprimer du partenaire">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
            `).join('');
            this.usersListTarget.innerHTML = usersHtml;
        }
    }

    // Filtrer les utilisateurs dans la recherche
    filterSearchUsers(event) {
        const searchTerm = event.target.value.toLowerCase();
        
        // Filtrer les utilisateurs qui correspondent
        const filteredUsers = this.allUsers.filter(user => {
            const fullName = `${user.prenom} ${user.nom}`.toLowerCase();
            const email = user.email.toLowerCase();
            return fullName.includes(searchTerm) || email.includes(searchTerm);
        });
        
        this.displayUsers(filteredUsers);
    }

    // Toggle dropdown ajout
    toggleAddDropdown(event) {
        event.stopPropagation();
        const isVisible = this.addDropdownTarget.style.display === 'block';
        
        if (isVisible) {
            this.addDropdownTarget.style.display = 'none';
        } else {
            // Positionner le dropdown
            const rect = event.currentTarget.getBoundingClientRect();
            this.addDropdownTarget.style.top = `${rect.bottom + 5}px`;
            this.addDropdownTarget.style.left = `${rect.left}px`;
            this.addDropdownTarget.style.width = `${rect.width}px`;
            this.addDropdownTarget.style.display = 'block';
            this.selectedUserIds.clear();
            this.displayAddUsers();
        }
    }

    // Afficher les utilisateurs disponibles pour l'ajout
    displayAddUsers(searchTerm = '') {
        const currentUserIds = this.allUsers.map(u => u.id);
        
        // Filtrer les utilisateurs non encore dans le partenaire
        let availableUsers = this.allAvailableUsers.filter(user => {
            if (currentUserIds.includes(user.id)) {
                return false;
            }
            return !user.partnaireId || user.partnaireId === '' || user.partnaireId === null;
        });
        
        // Appliquer le filtre de recherche
        if (searchTerm) {
            const term = searchTerm.toLowerCase();
            availableUsers = availableUsers.filter(user => {
                const fullName = `${user.prenom} ${user.nom}`.toLowerCase();
                const email = user.email.toLowerCase();
                return fullName.includes(term) || email.includes(term);
            });
        }
        
        if (availableUsers.length === 0) {
            this.addUsersListTarget.innerHTML = '<div class="empty-state">Aucun utilisateur disponible</div>';
        } else {
            const usersHtml = availableUsers.map(user => `
                <label class="user-checkbox-item">
                    <input type="checkbox" 
                        value="${user.id}" 
                        data-action="change->partenaires#toggleUserSelection"
                        ${this.selectedUserIds.has(user.id.toString()) ? 'checked' : ''}>
                    <div class="user-checkbox-info">
                        <strong>${user.prenom} ${user.nom}</strong>
                        <small>${user.email}</small>
                    </div>
                </label>
            `).join('');
            this.addUsersListTarget.innerHTML = usersHtml;
        }
    }

    filterAddUsers(event) {
        const searchTerm = event.target.value;
        this.displayAddUsers(searchTerm);
    }

    toggleUserSelection(event) {
        const userId = event.target.value;
        if (event.target.checked) {
            this.selectedUserIds.add(userId);
        } else {
            this.selectedUserIds.delete(userId);
        }
    }

    // Valider l'ajout des utilisateurs sélectionnés
    async validateAddUsers() {
        if (this.selectedUserIds.size === 0) {
            this.showNotification(this.selectUserValue, 'error');
            return;
        }
        
        try {
            window.showLoader();
            
            // Ajouter chaque utilisateur sélectionné
            for (const userId of this.selectedUserIds) {
                await fetch(this.getLocalizedUrl(`/administrateur/users/${userId}/edit`), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ partnaire_id: this.currentPartenaireId })
                });
            }
            
            window.hideLoader();
            const message = this.usersAddedValue.replace('{count}', this.selectedUserIds.size);
            this.showNotification(message, 'success');
            this.addDropdownTarget.style.display = 'none';
            this.selectedUserIds.clear();
            await this.loadUsersForPartenaire(this.currentPartenaireId);
        } catch (error) {
            window.hideLoader();
            console.error('Erreur:', error);
            this.showNotification('Une erreur est survenue', 'error');
        }
    }

    // Retirer un utilisateur du partenaire
    async removeUser(event) {
        const btn = event.currentTarget;
        const userId = btn.dataset.userId;
        
        try {
            window.showLoader();
            await fetch(this.getLocalizedUrl(`/administrateur/users/${userId}/edit`), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ partnaire_id: '' })
            });

            window.hideLoader();
            this.showNotification(this.userRemovedValue, 'success');
            await this.loadUsersForPartenaire(this.currentPartenaireId);
        } catch (error) {
            window.hideLoader();
            console.error('Erreur:', error);
            this.showNotification('Une erreur est survenue', 'error');
        }
    }

    // Soumission du formulaire
    async submitForm(event) {
        event.preventDefault();

        const partenaireId = this.partenaireIdTarget.value;

        const data = {
            nom: this.nomInputTarget.value,
            telephone: this.telephoneInputTarget.value,
            email: this.emailInputTarget.value,
            adresse: this.adresseInputTarget.value,
            ville: this.villeInputTarget.value,
            codePostal: this.codePostalInputTarget.value,
            siteWeb: this.siteWebInputTarget.value,
            description: this.descriptionInputTarget.value
        };

        try {
            let url, method;
            if (partenaireId) {
                url = this.getLocalizedUrl(`/administrateur/partenaires/${partenaireId}/edit`);
                method = 'POST';
            } else {
                url = this.getLocalizedUrl('/administrateur/partenaires/create');
                method = 'POST';
            }

            window.showLoader();
            const response = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            window.hideLoader();

            if (result.success) {
                this.showNotification(result.message, 'success');
                this.closeModal();
                setTimeout(() => window.location.reload(), 1000);
            } else {
                this.showNotification(result.message, 'error');
            }
        } catch (error) {
            window.hideLoader();
            console.error('Erreur:', error);
            this.showNotification('Une erreur est survenue', 'error');
        }
    }

    // Suppression
    async deleteSelected() {
        const selectedIds = this.checkboxTargets
            .filter(cb => cb.checked)
            .map(cb => parseInt(cb.value));

        if (selectedIds.length === 0) return;

        const confirmMessage = this.confirmDeleteValue.replace('{count}', selectedIds.length);
        if (!confirm(confirmMessage)) {
            return;
        }

        try {
            window.showLoader();
            const response = await fetch(this.getLocalizedUrl('/administrateur/partenaires/delete'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ ids: selectedIds })
            });

            const result = await response.json();
            window.hideLoader();

            if (result.success) {
                this.showNotification(result.message, 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                this.showNotification(result.message, 'error');
            }
        } catch (error) {
            window.hideLoader();
            console.error('Erreur:', error);
            this.showNotification('Une erreur est survenue', 'error');
        }
    }

    // Notification
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            background: ${type === 'success' ? '#ecfdf5' : '#fee2e2'};
            color: ${type === 'success' ? '#059669' : '#dc2626'};
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 2000;
            font-weight: 500;
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transition = 'opacity 0.3s';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
}
