import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'modal', 'modalTitle', 'form', 'userId', 
        'emailInput', 'usernameInput', 'prenomInput', 'nomInput',
        'passwordInput', 'passwordConfirmInput', 'passwordHelp', 'passwordError',
        'togglePassword', 'togglePasswordConfirm',
        'partenaireSelect',
        'roleCheckbox',
        'checkbox', 'checkAll', 'deleteBtn',
        'searchInput', 'roleFilter', 'partenaireFilter',
        'userRow', 'tableBody',
        'detailModal', 'detailEmail', 'detailUsername', 'detailPrenom', 
        'detailNom', 'detailPartenaire', 'detailRoles', 'editFromDetailBtn'
    ];

    static values = {
        csrfToken: String,
        addUser: String,
        editUser: String,
        passwordRequired: String,
        confirmDeleteMultiple: String,
        confirmDeleteSingle: String,
        success: String,
        error: String,
        passwordGenerated: String
    };

    connect() {
        console.log('Users controller loaded');
        this.currentUserData = null;
        
        // Exposer le controller globalement pour les onclick
        window.usersController = this;
    }

    // Helper pour obtenir l'URL avec la locale
    getLocalizedUrl(path) {
        const locale = document.documentElement.lang || 'fr';
        const url = `/${locale}${path}`;
        console.log('getLocalizedUrl:', path, '->', url, 'locale:', locale);
        return url;
    }

    // Empêcher le scroll de la page quand on scroll dans le modal
    preventBodyScroll() {
        document.body.style.overflow = 'hidden';
        
        // Empêcher le scroll de se propager depuis le modal
        const modals = document.querySelectorAll('.modal-content');
        modals.forEach(modal => {
            modal.addEventListener('wheel', (e) => {
                e.stopPropagation();
            }, { passive: true });
        });
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
        this.modalTitleTarget.textContent = this.addUserValue;
        this.userIdTarget.value = '';
        this.usernameInputTarget.value = '';
        this.prenomInputTarget.value = '';
        this.nomInputTarget.value = '';
        this.emailInputTarget.value = '';
        this.passwordInputTarget.value = '';
        this.passwordConfirmInputTarget.value = '';
        this.passwordInputTarget.required = true;
        this.passwordHelpTarget.style.display = 'none';
        this.passwordErrorTarget.style.display = 'none';
        this.partenaireSelectTarget.value = '';
        
        this.roleCheckboxTargets.forEach(cb => {
            cb.checked = cb.value === 'ROLE_USER';
        });

        this.modalTarget.classList.add('active');
        this.preventBodyScroll();
    }

    openEditModal(event) {
        const btn = event.currentTarget;
        const userId = btn.dataset.userId;
        const email = btn.dataset.userEmail;
        const username = btn.dataset.userUsername;
        const prenom = btn.dataset.userPrenom;
        const nom = btn.dataset.userNom;
        const partenaireId = btn.dataset.userPartenaireId;
        const roles = JSON.parse(btn.dataset.userRoles);

        this.modalTitleTarget.textContent = this.editUserValue;
        this.userIdTarget.value = userId;
        this.emailInputTarget.value = email;
        this.usernameInputTarget.value = username;
        this.prenomInputTarget.value = prenom;
        this.nomInputTarget.value = nom;
        this.passwordInputTarget.value = '';
        this.passwordConfirmInputTarget.value = '';
        this.passwordInputTarget.required = false;
        this.passwordHelpTarget.style.display = 'block';
        this.passwordErrorTarget.style.display = 'none';
        this.partenaireSelectTarget.value = partenaireId || '';

        this.roleCheckboxTargets.forEach(cb => {
            cb.checked = roles.includes(cb.value);
        });

        this.modalTarget.classList.add('active');
        this.preventBodyScroll();
    }

    closeModal() {
        this.modalTarget.classList.remove('active');
        this.formTarget.reset();
        this.passwordErrorTarget.style.display = 'none';
        this.passwordInputTarget.type = 'password';
        this.passwordConfirmInputTarget.type = 'password';
        this.togglePasswordTarget.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
        this.togglePasswordConfirmTarget.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
        this.enableBodyScroll();
    }

    // Soumission du formulaire
    async submitForm(event) {
        event.preventDefault();

        const password = this.passwordInputTarget.value;
        const passwordConfirm = this.passwordConfirmInputTarget.value;

        if (password || passwordConfirm) {
            if (password !== passwordConfirm) {
                this.passwordErrorTarget.style.display = 'block';
                return;
            }
        }
        this.passwordErrorTarget.style.display = 'none';

        const userId = this.userIdTarget.value;
        const email = this.emailInputTarget.value;
        const username = this.usernameInputTarget.value;
        const prenom = this.prenomInputTarget.value;
        const nom = this.nomInputTarget.value;
        const partenaireId = this.partenaireSelectTarget.value;
        const roles = this.roleCheckboxTargets
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        const data = {
            email,
            username,
            prenom,
            nom,
            roles,
            partnaire_id: partenaireId || null,
            _token: this.csrfTokenValue
        };

        if (password) {
            data.password = password;
        }

        try {
            let url, method;
            if (userId) {
                url = this.getLocalizedUrl(`/administrateur/users/${userId}/edit`);
                method = 'POST';
            } else {
                url = this.getLocalizedUrl('/administrateur/users/create');
                method = 'POST';
                if (!password) {
                    alert(this.passwordRequiredValue);
                    return;
                }
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

        // Stocker les IDs pour la confirmation
        this.selectedIdsToDelete = selectedIds;
        
        // Afficher la modale via la fonction globale
        showDeleteMultipleUsersModal(selectedIds.length);
    }

    async executeDeleteMultiple() {
        if (!this.selectedIdsToDelete || this.selectedIdsToDelete.length === 0) return;

        try {
            window.showLoader();
            const response = await fetch(this.getLocalizedUrl('/administrateur/users/delete'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ ids: this.selectedIdsToDelete, _token: this.csrfTokenValue })
            });

            const result = await response.json();
            window.hideLoader();

            if (result.success) {
                this.showNotification(result.message, 'success');
                this.selectedIdsToDelete = null;
                setTimeout(() => window.location.reload(), 1000);
            } else {
                this.showNotification(result.message, 'error');
            }
        } catch (error) {
            window.hideLoader();
            console.error('Erreur:', error);
            this.showNotification(this.errorValue, 'error');
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

    // Générateur de mot de passe
    generatePassword() {
        const length = 16;
        const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        let password = '';
        
        for (let i = 0; i < length; i++) {
            const randomIndex = Math.floor(Math.random() * charset.length);
            password += charset[randomIndex];
        }
        
        this.passwordInputTarget.value = password;
        this.passwordConfirmInputTarget.value = password;
        this.passwordInputTarget.type = 'text';
        this.passwordConfirmInputTarget.type = 'text';
        this.passwordErrorTarget.style.display = 'none';
        
        this.showNotification(this.passwordGeneratedValue, 'success');
    }

    // Afficher/masquer le mot de passe
    togglePasswordVisibility() {
        const input = this.passwordInputTarget;
        const button = this.togglePasswordTarget;
        
        if (input.type === 'password') {
            input.type = 'text';
            button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';
        } else {
            input.type = 'password';
            button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
        }
    }

    togglePasswordConfirmVisibility() {
        const input = this.passwordConfirmInputTarget;
        const button = this.togglePasswordConfirmTarget;
        
        if (input.type === 'password') {
            input.type = 'text';
            button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';
        } else {
            input.type = 'password';
            button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
        }
    }

    // Filtrage des utilisateurs
    filterUsers() {
        const searchTerm = this.searchInputTarget.value.toLowerCase();
        const roleFilter = this.roleFilterTarget.value;
        const partenaireFilter = this.partenaireFilterTarget.value;

        this.userRowTargets.forEach(row => {
            const email = row.dataset.email?.toLowerCase() || '';
            const username = row.dataset.username?.toLowerCase() || '';
            const prenom = row.dataset.prenom?.toLowerCase() || '';
            const nom = row.dataset.nom?.toLowerCase() || '';
            const partenaireId = row.dataset.partenaireId || '';
            const roles = JSON.parse(row.dataset.roles || '[]');

            // Recherche par mots-clés
            const matchesSearch = !searchTerm || 
                username.includes(searchTerm) || 
                prenom.includes(searchTerm) || 
                nom.includes(searchTerm) ||
                email.includes(searchTerm);

            // Filtre par rôle
            const matchesRole = !roleFilter || roles.includes(roleFilter);

            // Filtre par partenaire
            let matchesPartenaire = true;
            if (partenaireFilter) {
                if (partenaireFilter === 'none') {
                    matchesPartenaire = !partenaireId || partenaireId === '';
                } else {
                    matchesPartenaire = String(partenaireId) === String(partenaireFilter);
                }
            }

            // Afficher ou masquer la ligne
            if (matchesSearch && matchesRole && matchesPartenaire) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    resetFilters() {
        this.searchInputTarget.value = '';
        this.roleFilterTarget.value = '';
        this.partenaireFilterTarget.value = '';
        this.filterUsers();
    }

    // Affichage des détails utilisateur
    showUserDetail(event) {
        // Ne pas ouvrir si on clique sur la checkbox ou le bouton modifier
        if (event.target.closest('input[type="checkbox"]') || event.target.closest('.btn-icon')) {
            return;
        }

        const row = event.currentTarget;
        const userId = row.dataset.userId;
        const email = row.dataset.email;
        const username = row.dataset.username;
        const prenom = row.dataset.prenom;
        const nom = row.dataset.nom;
        const partenaireName = row.dataset.partnaireName;
        const partenaireId = row.dataset.partenaireId;
        const roles = JSON.parse(row.dataset.roles || '[]');

        // Stocker les données pour l'édition
        this.currentUserData = {
            userId,
            email,
            username,
            prenom,
            nom,
            partenaireId,
            roles
        };

        // Remplir les détails
        this.detailEmailTarget.textContent = email || '-';
        this.detailUsernameTarget.textContent = username || '-';
        this.detailPrenomTarget.textContent = prenom || '-';
        this.detailNomTarget.textContent = nom || '-';
        this.detailPartenaireTarget.textContent = partenaireName || 'Aucun';
        
        // Afficher les rôles de manière lisible
        const roleLabels = roles.map(role => {
            if (role === 'ROLE_ADMINISTRATEUR') return 'Administrateur';
            if (role === 'ROLE_ADMINISTRATION') return 'Administration';
            if (role === 'ROLE_USER') return 'Utilisateur';
            return role;
        });
        this.detailRolesTarget.textContent = roleLabels.join(', ') || '-';

        this.detailModalTarget.classList.add('active');
        this.preventBodyScroll();
    }

    closeDetailModal() {
        this.detailModalTarget.classList.remove('active');
        this.currentUserData = null;
        this.enableBodyScroll();
    }

    editFromDetail() {
        if (!this.currentUserData) return;

        // Stocker les données localement avant de fermer la modal
        const userData = { ...this.currentUserData };

        // Fermer la modal de détails
        this.closeDetailModal();

        // Ouvrir la modal d'édition avec les données
        this.modalTitleTarget.textContent = 'Modifier l\'utilisateur';
        this.userIdTarget.value = userData.userId;
        this.emailInputTarget.value = userData.email;
        this.usernameInputTarget.value = userData.username;
        this.prenomInputTarget.value = userData.prenom;
        this.nomInputTarget.value = userData.nom;
        this.passwordInputTarget.value = '';
        this.passwordConfirmInputTarget.value = '';
        this.passwordInputTarget.required = false;
        this.passwordHelpTarget.style.display = 'block';
        this.passwordErrorTarget.style.display = 'none';
        this.partenaireSelectTarget.value = userData.partenaireId || '';

        this.roleCheckboxTargets.forEach(cb => {
            cb.checked = userData.roles.includes(cb.value);
        });

        this.modalTarget.classList.add('active');
    }

    async deleteFromDetail() {
        if (!this.currentUserData) return;

        const userId = this.currentUserData.userId;
        const username = this.currentUserData.username || this.currentUserData.email;

        // Stocker les données pour la confirmation
        this.userIdToDelete = userId;
        
        // Afficher la modale via la fonction globale
        showDeleteUserModal(username, userId);
    }

    async executeDelete(userId) {
        if (!userId) return;

        try {
            window.showLoader();
            const response = await fetch(this.getLocalizedUrl('/administrateur/users/delete'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ ids: [parseInt(userId)], _token: this.csrfTokenValue })
            });

            const result = await response.json();
            window.hideLoader();

            if (result.success) {
                this.closeDetailModal();
                this.showNotification(result.message, 'success');
                this.userIdToDelete = null;
                setTimeout(() => window.location.reload(), 1000);
            } else {
                this.showNotification(result.message, 'error');
            }
        } catch (error) {
            window.hideLoader();
            console.error('Erreur:', error);
            this.showNotification(this.errorValue, 'error');
        }
    }
}
