import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['accordion', 'content', 'icon', 'editForm', 'preview', 'section', 'categoryCheckbox'];
    static values = {
        saveChanges: String,
        saving: String
    }

    connect() {
        console.log('Edit entreprise controller connected');
        this.setupAutoSave();
        this.toggleSections();
        
        // Store entreprise ID for later use
        this.entrepriseId = this.element.dataset.entrepriseId;
        
        // Exposer le controller globalement pour les onclick
        window.editEntrepriseController = this;
    }

    toggleSections() {
        const checkedCategories = this.categoryCheckboxTargets
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        this.sectionTargets.forEach(section => {
            const category = section.dataset.category;
            if (checkedCategories.includes(category)) {
                section.style.display = '';
            } else {
                section.style.display = 'none';
            }
        });
    }

    toggleAccordion(event) {
        // Find the closest accordion-header that was clicked
        const header = event.currentTarget.closest('.accordion-header');
        if (!header) return;
        
        // Get the next sibling which should be accordion-content
        const content = header.nextElementSibling;
        if (!content || !content.classList.contains('accordion-content')) return;
        
        // Find the icon inside the header
        const icon = header.querySelector('.accordion-icon i');
        
        // Toggle states
        const isOpen = content.classList.contains('active');
        
        if (isOpen) {
            content.classList.remove('active', 'open');
            header.classList.remove('active');
            if (icon) icon.style.transform = 'rotate(0deg)';
        } else {
            content.classList.add('active', 'open');
            header.classList.add('active');
            if (icon) icon.style.transform = 'rotate(180deg)';
        }
    }

    toggleEdit(event) {
        const listItem = event.currentTarget.closest('.list-item');
        if (!listItem) return;
        
        const preview = listItem.querySelector('[data-edit-entreprise-target="preview"]');
        const editForm = listItem.querySelector('[data-edit-entreprise-target="editForm"]');
        
        if (editForm) {
            if (editForm.classList.contains('active')) {
                editForm.classList.remove('active');
                if (preview) preview.style.display = '';
            } else {
                editForm.classList.add('active');
                if (preview) preview.style.display = 'none';
            }
        }
    }

    cancelAdd(event) {
        event.preventDefault();
        const button = event.currentTarget;
        
        // WP2 Special case within list-stack
        const wp2Section = button.closest('[data-category="wp2"]');
        if (wp2Section) {
            const listItem = button.closest('.list-item');
            if (listItem) {
                listItem.remove();
                
                // Restore Button visibility
                const addButton = wp2Section.querySelector('.btn-add-item');
                if (addButton) {
                    addButton.style.display = '';
                }
                return;
            }
        }

        // Standard list item (WP5/6/7/Relation)
        const listItem = button.closest('.list-item');
        if (listItem && listItem.classList.contains('new-item')) {
            listItem.remove();
            return;
        }
    }

    confirmDelete(event) {
        event.preventDefault();
        const wpType = event.currentTarget.dataset.wpType;
        const wpId = event.currentTarget.dataset.wpId;
        const wpName = event.currentTarget.dataset.wpName || 'ce work package';
        
        // Créer et afficher le modal de confirmation
        const modalId = `deleteWpModal-${wpType}-${wpId}`;
        let modal = document.getElementById(modalId);
        
        if (!modal) {
            // Créer le modal s'il n'existe pas
            modal = this.createDeleteModal(modalId, wpName, wpType, wpId);
            document.body.appendChild(modal);
        }
        
        // Ouvrir le modal
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        // Ajouter backdrop
        const backdrop = document.createElement('div');
        backdrop.className = 'bg-gray-900 bg-opacity-50 fixed inset-0 z-40';
        backdrop.id = 'modal-backdrop-' + modalId;
        backdrop.onclick = () => this.closeDeleteModal(modalId);
        document.body.insertBefore(backdrop, modal);
    }
    
    createDeleteModal(modalId, wpName, wpType, wpId) {
        const modal = document.createElement('div');
        modal.id = modalId;
        modal.tabIndex = -1;
        modal.setAttribute('aria-hidden', 'true');
        modal.className = 'hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full';
        
        modal.innerHTML = `
            <div class="relative p-4 w-full max-w-md h-full md:h-auto">
                <div class="relative p-4 text-center bg-white rounded-lg shadow sm:p-5">
                    <button type="button" onclick="editEntrepriseController.closeDeleteModal('${modalId}')" class="text-gray-400 absolute top-2.5 right-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center">
                        <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        <span class="sr-only">Fermer</span>
                    </button>
                    <svg class="text-gray-400 w-11 h-11 mb-3.5 mx-auto" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                    <p class="mb-4 text-gray-500">Êtes-vous sûr de vouloir supprimer ce work package ?</p>
                    <p class="mb-4 font-semibold text-gray-900">${wpName}</p>
                    <div class="flex justify-center items-center space-x-4">
                        <button onclick="editEntrepriseController.closeDeleteModal('${modalId}')" type="button" class="py-2 px-3 text-sm font-medium text-gray-500 bg-white rounded-lg border border-gray-200 hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-primary-300 hover:text-gray-900 focus:z-10">
                            Non, annuler
                        </button>
                        <button onclick="editEntrepriseController.executeDelete('${wpType}', '${wpId}', '${modalId}')" type="button" class="py-2 px-3 text-sm font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300">
                            Oui, supprimer
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        return modal;
    }
    
    closeDeleteModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        const backdrop = document.getElementById('modal-backdrop-' + modalId);
        if (backdrop) {
            backdrop.remove();
        }
    }
    
    executeDelete(wpType, wpId, modalId) {
        this.closeDeleteModal(modalId);
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_wp">
            <input type="hidden" name="wp_type" value="${wpType}">
            <input type="hidden" name="wp_id" value="${wpId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }


    addWorkPackage(event) {
        const wpType = event.currentTarget.dataset.wpType;
        const accordionItem = event.currentTarget.closest('.accordion-item');
        
        if (!accordionItem) return;

        // Special handling for WP2 (Single Item)
        if (wpType === 'wp2') {
            const container = accordionItem.querySelector('.accordion-body');
            const addButton = container.querySelector('.btn-add-item');
            
            if (addButton) {
                // Hide button instead of removing placeholder
                addButton.style.display = 'none';
                
                const formHtml = `
                    <div class="list-item new-item">
                        <div class="list-item-header">
                            <div class="list-item-title">
                                <span class="badge wp2">Nouveau</span>
                                Diagnostic DMAO
                            </div>
                        </div>
                        <div class="inline-edit-form active" style="display:block">
                            <form method="POST" data-action="submit->edit-entreprise#quickSave">
                                <input type="hidden" name="action" value="create_wp">
                                <input type="hidden" name="wp_type" value="wp2">
                                
                                <div class="form-row">
                                    <div class="form-group"><label>Score DMAO</label><input type="text" name="score_dmao"></div>
                                    <div class="form-group"><label>Digital Strategy</label><input type="text" name="digital_strategy"></div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group"><label>Digital Readiness</label><input type="text" name="digital_readiness"></div>
                                    <div class="form-group"><label>Human Centric</label><input type="text" name="human_centric"></div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group"><label>Data Governance</label><input type="text" name="data_governance"></div>
                                    <div class="form-group"><label>AI</label><input type="text" name="ai"></div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group"><label>Green</label><input type="text" name="green"></div>
                                    <div class="form-group"><label>Score DMA1</label><input type="text" name="score_dma1"></div>
                                </div>
                                <div style="text-align:right;">
                                    <button type="button" class="btn-cancel-mini" data-action="click->edit-entreprise#cancelAdd">Annuler</button>
                                    <button type="submit" class="btn-save-mini"><i class="fi fi-rs-disk"></i> Créer WP2</button>
                                </div>
                            </form>
                        </div>
                    </div>
                `;
                
                let listStack = container.querySelector('.list-stack');
                if (!listStack) {
                    listStack = document.createElement('div');
                    listStack.className = 'list-stack';
                    container.insertBefore(listStack, addButton);
                }
                
                listStack.insertAdjacentHTML('beforeend', formHtml);
                this.setupAutoSave();
                return;
            }
        }

        // Handling List Items (WP5, WP6, WP7, Relations)
        const listStack = accordionItem.querySelector('.list-stack');
        // If list stack doesn't exist (e.g. empty section), create it before the button
        let targetContainer = listStack;
        if (!targetContainer) {
            targetContainer = document.createElement('div');
            targetContainer.className = 'list-stack';
            const btn = accordionItem.querySelector('.btn-add-item');
            if (btn) {
                btn.parentNode.insertBefore(targetContainer, btn);
            } else {
                accordionItem.querySelector('.accordion-body').appendChild(targetContainer);
            }
        }

        let newFormHtml = '';
        const commonHiddenInputs = `<input type="hidden" name="action" value="create_wp"><input type="hidden" name="wp_type" value="${wpType}">`;

        if (wpType === 'wp5_event') {
            newFormHtml = `
                <div class="list-item new-item">
                    <div class="list-item-header">
                        <div class="list-item-title"><span class="badge">Nouveau Event</span></div>
                    </div>
                    <div class="inline-edit-form active" style="display:block">
                        <form method="POST" data-action="submit->edit-entreprise#quickSave">
                            ${commonHiddenInputs}
                            <div class="form-row">
                                <div class="form-group"><label>Nom (EN)</label><input type="text" name="event_name_english" required></div>
                                <div class="form-group"><label>Nom (Orig)</label><input type="text" name="event_name_original"></div>
                            </div>
                            <div class="form-row">
                                <div class="form-group"><label>Début</label><input type="date" name="start_date"></div>
                                <div class="form-group"><label>Fin</label><input type="date" name="end_date"></div>
                            </div>
                            <div class="form-group"><label>Participants</label><input type="number" name="attendees"></div>
                            <div style="text-align:right;">
                                <button type="button" class="btn-cancel-mini" data-action="click->edit-entreprise#cancelAdd">Annuler</button>
                                <button type="submit" class="btn-save-mini">Créer</button>
                            </div>
                        </form>
                    </div>
                </div>`;
        } else if (wpType === 'wp5_formation') {
            newFormHtml = `
                <div class="list-item new-item">
                    <div class="list-item-header">
                        <div class="list-item-title"><span class="badge" style="background:#fff3e0; color:#e65100;">Nouvelle Formation</span></div>
                    </div>
                    <div class="inline-edit-form active" style="display:block">
                        <form method="POST" data-action="submit->edit-entreprise#quickSave">
                            ${commonHiddenInputs}
                            <div class="form-row">
                                <div class="form-group"><label>Responsable</label><input type="text" name="responsible"></div>
                                <div class="form-group"><label>Technologie</label><input type="text" name="technology" required></div>
                            </div>
                            <div class="form-row">
                                <div class="form-group"><label>Prix Service</label><input type="text" name="service_price"></div>
                                <div class="form-group"><label>Prix Facturé</label><input type="text" name="price_invoiced"></div>
                            </div>
                            <div style="text-align:right;">
                                <button type="button" class="btn-cancel-mini" data-action="click->edit-entreprise#cancelAdd">Annuler</button>
                                <button type="submit" class="btn-save-mini">Créer</button>
                            </div>
                        </form>
                    </div>
                </div>`;
        } else if (wpType === 'wp6') {
            newFormHtml = `
                <div class="list-item new-item">
                    <div class="list-item-header">
                        <div class="list-item-title"><span class="badge wp6">Nouveau Projet</span></div>
                    </div>
                    <div class="inline-edit-form active" style="display:block">
                        <form method="POST" data-action="submit->edit-entreprise#quickSave">
                            ${commonHiddenInputs}
                            <div class="form-row">
                                <div class="form-group"><label>Responsable</label><input type="text" name="responsible"></div>
                                <div class="form-group"><label>Technologie</label><input type="text" name="technology" required></div>
                            </div>
                            <div class="form-row">
                                <div class="form-group"><label>Prix Service</label><input type="text" name="service_price"></div>
                                <div class="form-group"><label>Prix Facturé</label><input type="text" name="price_invoiced"></div>
                            </div>
                            <div class="form-row">
                                <div class="form-group"><label>Début</label><input type="date" name="start_date"></div>
                                <div class="form-group"><label>Fin</label><input type="date" name="finish_date"></div>
                            </div>
                            <div style="text-align:right;">
                                <button type="button" class="btn-cancel-mini" data-action="click->edit-entreprise#cancelAdd">Annuler</button>
                                <button type="submit" class="btn-save-mini">Créer</button>
                            </div>
                        </form>
                    </div>
                </div>`;
        } else if (wpType === 'wp7') {
             newFormHtml = `
                <div class="list-item new-item">
                    <div class="list-item-header">
                        <div class="list-item-title"><span class="badge wp7">Nvel Invest.</span></div>
                    </div>
                    <div class="inline-edit-form active" style="display:block">
                        <form method="POST" data-action="submit->edit-entreprise#quickSave">
                            ${commonHiddenInputs}
                            <div class="form-row">
                                <div class="form-group"><label>Type</label><input type="text" name="type_investment" required></div>
                                <div class="form-group"><label>Financement</label><input type="text" name="source_financing"></div>
                            </div>
                            <div class="form-row">
                                <div class="form-group"><label>Déclencheur</label><input type="text" name="amount_trigger"></div>
                                <div class="form-group"><label>Obtenu</label><input type="text" name="amount_obtained"></div>
                            </div>
                            <div class="form-group"><label>Responsable</label><input type="text" name="responsible"></div>
                            <div style="text-align:right;">
                                <button type="button" class="btn-cancel-mini" data-action="click->edit-entreprise#cancelAdd">Annuler</button>
                                <button type="submit" class="btn-save-mini">Créer</button>
                            </div>
                        </form>
                    </div>
                </div>`;
        } else if (wpType === 'relation') {
            newFormHtml = `
                <div class="list-item new-item">
                    <div class="list-item-header">
                        <div class="list-item-title"><span class="badge relation">Nouvelle Relation</span></div>
                    </div>
                    <div class="inline-edit-form active" style="display:block">
                        <form method="POST" data-action="submit->edit-entreprise#quickSave">
                            ${commonHiddenInputs}
                            <div class="form-row">
                                <div class="form-group"><label>Responsable</label><input type="text" name="responsible"></div>
                                <div class="form-group"><label>Sujet</label><input type="text" name="technology" required></div>
                            </div>
                            <div class="form-row">
                                <div class="form-group"><label>Prix Service</label><input type="text" name="service_price"></div>
                                <div class="form-group"><label>Prix Facturé</label><input type="text" name="price_invoiced"></div>
                            </div>
                            <div style="text-align:right;">
                                <button type="button" class="btn-cancel-mini" data-action="click->edit-entreprise#cancelAdd">Annuler</button>
                                <button type="submit" class="btn-save-mini">Créer</button>
                            </div>
                        </form>
                    </div>
                </div>`;
        }

        if (newFormHtml) {
            targetContainer.insertAdjacentHTML('beforeend', newFormHtml);
            
            // Scroll to the new item
            const newItem = targetContainer.lastElementChild;
            setTimeout(() => {
                newItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
                // Optional: Focus the first input
                const firstInput = newItem.querySelector('input');
                if (firstInput) firstInput.focus();
            }, 100);

            this.setupAutoSave();
        }
    }

    setupAutoSave() {
        // Détecter les modifications dans les formulaires
        const forms = this.element.querySelectorAll('form');
        forms.forEach(form => {
            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                input.addEventListener('change', () => {
                    this.markAsModified(form);
                });
            });
        });
    }

    markAsModified(form) {
        const saveBtn = form.querySelector('button[type="submit"]');
        if (saveBtn && !saveBtn.classList.contains('modified')) {
            saveBtn.classList.add('modified');
            saveBtn.innerHTML = `<i class="fi fi-rs-disk"></i> ${this.saveChangesValue}`;
        }
    }

    showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#6b7280'};
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            font-size: 0.875rem;
            z-index: 9999;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease;
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    quickSave(event) {
        event.preventDefault();
        const form = event.currentTarget.closest('form');
        
        if (!form) return;
        
        const saveBtn = form.querySelector('button[type="submit"]');
        
        // Validation basique
        const requiredInputs = form.querySelectorAll('input[required]');
        for (let input of requiredInputs) {
            if (!input.value.trim()) {
                this.showNotification('Veuillez remplir tous les champs obligatoires', 'error');
                input.focus();
                return;
            }
        }
        
        // Animation de sauvegarde
        if (saveBtn) {
            saveBtn.innerHTML = `<i class="fi fi-rs-spinner"></i> ${this.savingValue}`;
            saveBtn.disabled = true;
        }
        
        form.submit();
    }

    confirmDelete(event) {
        const button = event.currentTarget;
        const wpType = button.dataset.wpType;
        const wpId = button.dataset.wpId;
        const wpName = button.dataset.wpName;

        // Stocker les données pour la suppression
        this.deleteWpType = wpType;
        this.deleteWpId = wpId;

        // Afficher la modale via la fonction globale
        if (typeof showDeleteWpModal === 'function') {
            showDeleteWpModal(wpName, wpType);
        }
    }

    executeDelete() {
        if (!this.deleteWpType || !this.deleteWpId) return;

        // Créer et soumettre le formulaire de suppression
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = window.location.href;

        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete_wp';
        form.appendChild(actionInput);

        const typeInput = document.createElement('input');
        typeInput.type = 'hidden';
        typeInput.name = 'wp_type';
        typeInput.value = this.deleteWpType;
        form.appendChild(typeInput);

        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'wp_id';
        idInput.value = this.deleteWpId;
        form.appendChild(idInput);

        document.body.appendChild(form);
        form.submit();
    }
}
