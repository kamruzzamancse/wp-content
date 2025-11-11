document.addEventListener('DOMContentLoaded', function () {
    let isProcessing = false;

    // ---------------------------
    // AJAX wrapper
    // ---------------------------
    async function ajaxFetch(formData) {
        try {
            const response = await fetch(rtClientAjax.ajax_url, { method: 'POST', body: formData });
            return await response.json();
        } catch (err) {
            return { success: false, data: err.message };
        }
    }

    // ---------------------------
    // Debounce
    // ---------------------------
    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // ---------------------------
    // Show notification
    // ---------------------------
    function showNotification(message, type = 'success') {
        document.querySelectorAll('.client-notification').forEach(n => n.remove());
        const notification = document.createElement('div');
        notification.className = `client-notification ${type}`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            background: ${type === 'success' ? '#4CAF50' : '#f44336'};
            color: white;
            border-radius: 4px;
            z-index: 10000;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease;
        `;
        notification.textContent = message;
        document.body.appendChild(notification);
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }
        }, 300);

        if (!document.querySelector('#notification-styles')) {
            const style = document.createElement('style');
            style.id = 'notification-styles';
            style.textContent = `
                @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
                @keyframes slideOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } }
            `;
            document.head.appendChild(style);
        }
    }

    // ---------------------------
    // Fetch clients
    // ---------------------------
    window.fetchClients = async function({ page = 1, rows = 10, search = '', bodyId, paginationId }) {
        const formData = new FormData();
        formData.append('action', 'fetch_clients_ajax');
        formData.append('nonce', rtClientAjax.edit_nonce);
        formData.append('page', page);
        formData.append('rows', rows);
        formData.append('search', search);

        const result = await ajaxFetch(formData);
        const tbody = document.getElementById(bodyId);
        const pagination = document.getElementById(paginationId);
        if (!tbody || !pagination) return;

        tbody.innerHTML = '';
        pagination.innerHTML = '';

        if (result.success && result.data.clients.length > 0) {
            result.data.clients.forEach(client => {
                const tr = document.createElement('tr');
                tr.dataset.clientId = client.client_id;
                tr.innerHTML = `
                    <td><img src="${client.profile_picture || rtClientAjax.default_avatar}" style="width:40px;height:40px;border-radius:50%;object-fit:cover;"></td>
                    <td class="client-name-cell" style="cursor: pointer; color: #007bff; font-weight: 500;">
                        ${client.full_name}
                    </td>
                    <td>${client.email}</td>
                    <td>${client.phone || ''}</td>
                    <td>${client.note || ''}</td>
                    <td>${client.status || ''}</td>
                    <td>
                        <span class="editClientBtn" style="cursor:pointer; margin-right: 10px;">✏️</span>
                        <span class="deleteClientBtn" style="cursor:pointer; margin-right: 10px;">🗑️</span>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            for (let i = 1; i <= result.data.total_pages; i++) {
                const btn = document.createElement('button');
                btn.textContent = i;
                if (i === page) btn.classList.add('active');
                btn.addEventListener('click', () => fetchClients({ page: i, rows, search, bodyId, paginationId }));
                pagination.appendChild(btn);
            }

            setupRowButtons(bodyId);
            setupClientDetailsHandlers();
        } else {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;">No clients found</td></tr>`;
        }
    };

    // ---------------------------
    // Client Details Handlers
    // ---------------------------
    function setupClientDetailsHandlers() {
        document.removeEventListener('click', handleClientNameClick);
        document.removeEventListener('click', handleViewButtonClick);
        document.addEventListener('click', handleClientNameClick);
        document.addEventListener('click', handleViewButtonClick);
    }

    function handleClientNameClick(e) {
        const clientNameCell = e.target.closest('.client-name-cell');
        if (clientNameCell) {
            e.preventDefault();
            const row = clientNameCell.closest('tr');
            const clientId = row.dataset.clientId;
            if (clientId && typeof window.openClientDetailsModal === 'function') {
                window.openClientDetailsModal(clientId);
            }
        }
    }

    function handleViewButtonClick(e) {
        const viewBtn = e.target.closest('.viewClientBtn');
        if (viewBtn) {
            e.preventDefault();
            const clientId = viewBtn.dataset.clientId;
            if (clientId && typeof window.openClientDetailsModal === 'function') {
                window.openClientDetailsModal(clientId);
            }
        }
    }

    // ---------------------------
    // Create client
    // ---------------------------
    const createForm = document.getElementById('createRealtorClientForm');
    if (createForm) {
        createForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn.disabled) return;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating...';

            const formData = new FormData(this);
            formData.append('action', 'create_realtor_client_ajax');
            formData.append('nonce', rtClientAjax.create_nonce);

            try {
                const response = await fetch(rtClientAjax.ajax_url, { method: 'POST', body: formData });
                const result = await response.json();
                
                if (result.success) {
                    showNotification('Client created successfully!');
                    this.reset();
                    document.getElementById('createRealtorClientPreviewAvatar').src = rtClientAjax.default_avatar;
                    document.getElementById('rmRealtorClientCreateModal').style.display = 'none';
                    
                    setTimeout(() => {
                        fetchClients({
                            page: 1,
                            rows: parseInt(document.getElementById('addressBookRows').value, 10),
                            search: document.getElementById('addressBookSearch').value.trim(),
                            bodyId: 'addressBookBody',
                            paginationId: 'addressBookPagination'
                        });
                    }, 500);
                } else {
                    showNotification('Error: ' + result.data, 'error');
                }
            } catch (error) {
                showNotification('Network error. Please try again.', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Create Client';
            }
        });
    }

    // ---------------------------
    // Edit client
    // ---------------------------
    const editForm = document.getElementById('editRealtorClientForm');
    if (editForm) {
        editForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const submitBtn = editForm.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Updating...';

            const formData = new FormData(editForm);
            formData.append('action', 'update_realtor_client_ajax');
            formData.append('nonce', rtClientAjax.edit_nonce);

            try {
                const res = await fetch(rtClientAjax.ajax_url, { method: 'POST', body: formData });
                const result = await res.json();

                if (result.success) {
                    showNotification('Client updated successfully!');
                    document.getElementById('rmRealtorClientEditModal').style.display = 'none';
                    if (typeof fetchClients === 'function') {
                        fetchClients({
                            page: 1,
                            rows: parseInt(document.getElementById('addressBookRows').value, 10),
                            search: document.getElementById('addressBookSearch').value.trim(),
                            bodyId: 'addressBookBody',
                            paginationId: 'addressBookPagination'
                        });
                    }
                } else {
                    showNotification('Update failed: ' + result.data, 'error');
                }
            } catch (err) {
                showNotification('Network error: ' + err.message, 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Update Client';
            }
        });
    }

    // ---------------------------
    // Profile picture preview
    // ---------------------------
    const profileInputs = [
        { input: 'create_realtor_client_profile_picture', preview: 'createRealtorClientPreviewAvatar' },
        { input: 'edit_realtor_client_profile_picture', preview: 'editRealtorClientPreviewAvatar' }
    ];
    profileInputs.forEach(item => {
        const inputEl = document.getElementById(item.input);
        const previewEl = document.getElementById(item.preview);
        if (inputEl && previewEl) {
            inputEl.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => previewEl.src = e.target.result;
                    reader.readAsDataURL(file);
                }
            });
        }
    });

    // ---------------------------
    // Modal functionality
    // ---------------------------
    function initializeModalFunctionality() {
        const createModal = document.getElementById('rmRealtorClientCreateModal');
        const addContactBtn = document.querySelector('.ab-btn-create');
        const closeBtn = document.getElementById('closeRealtorClientCreateModal');

        if (addContactBtn && createModal) addContactBtn.addEventListener('click', () => createModal.style.display = 'flex');
        if (closeBtn && createModal) {
            closeBtn.addEventListener('click', () => createModal.style.display = 'none');
            createModal.addEventListener('click', e => { if (e.target === createModal) createModal.style.display = 'none'; });
        }
    }

    // ---------------------------
    // Edit/Delete row buttons
    // ---------------------------
    function setupRowButtons(bodyId) {
        const tbody = document.getElementById(bodyId);
        if (!tbody) return;

        tbody.querySelectorAll('.editClientBtn').forEach(btn => {
            btn.addEventListener('click', async function () {
                const row = this.closest('tr');
                const clientId = row.dataset.clientId;
                if (!clientId) return;

                const modal = document.getElementById('rmRealtorClientEditModal');
                if (!modal) return;

                modal.style.display = 'flex';

                try {
                    const formData = new FormData();
                    formData.append('action', 'fetch_realtor_client_ajax');
                    formData.append('nonce', rtClientAjax.edit_nonce);
                    formData.append('client_id', clientId);

                    const response = await fetch(rtClientAjax.ajax_url, { method: 'POST', body: formData });
                    const result = await response.json();
                    if (!result.success) throw new Error(result.data || 'Failed to fetch client data');

                    const client = result.data;

                    document.getElementById('edit_realtor_client_id').value = client.client_id;
                    document.getElementById('edit_realtor_client_full_name').value = client.full_name || '';
                    document.getElementById('edit_realtor_client_email').value = client.email || '';
                    document.getElementById('edit_realtor_client_phone').value = client.phone || '';
                    document.getElementById('edit_realtor_client_notes').value = client.note || '';
                    document.getElementById('edit_realtor_client_status').value = client.status || '';

                    const previewAvatar = document.getElementById('editRealtorClientPreviewAvatar');
                    if (previewAvatar) previewAvatar.src = client.profile_picture || rtClientAjax.default_avatar;

                } catch (error) {
                    alert('Error loading client data: ' + error.message);
                }
            });
        });

        tbody.querySelectorAll('.deleteClientBtn').forEach(btn => {
            btn.addEventListener('click', async function () {
                const row = this.closest('tr');
                const clientId = row.dataset.clientId;
                if (!clientId) return;
                if (!confirm('Are you sure you want to delete this client?')) return;

                const deleteBtn = this;
                const originalText = deleteBtn.textContent;
                deleteBtn.disabled = true;
                deleteBtn.textContent = 'Deleting...';

                try {
                    const formData = new FormData();
                    formData.append('action', 'delete_realtor_client_ajax');
                    formData.append('nonce', rtClientAjax.delete_nonce);
                    formData.append('client_id', clientId);

                    const response = await fetch(rtClientAjax.ajax_url, { method: 'POST', body: formData });
                    const result = await response.json();

                    if (result.success) {
                        showNotification('Client deleted successfully!', 'success');
                        await fetchClients({
                            page: 1,
                            rows: parseInt(document.getElementById('addressBookRows').value, 10),
                            search: document.getElementById('addressBookSearch').value.trim(),
                            bodyId: 'addressBookBody',
                            paginationId: 'addressBookPagination'
                        });
                    } else {
                        throw new Error(result.data || 'Delete failed');
                    }

                } catch (error) {
                    showNotification('Error deleting client: ' + error.message, 'error');
                } finally {
                    deleteBtn.disabled = false;
                    deleteBtn.textContent = originalText;
                }
            });
        });
    }

    // ---------------------------
    // Search & Rows
    // ---------------------------
    function setupSearchAndRows(searchInputId, rowsSelectId, bodyId, paginationId) {
        const searchInput = document.getElementById(searchInputId);
        const rowsSelect = document.getElementById(rowsSelectId);
        if (!searchInput || !rowsSelect) return;

        const fetchTable = debounce(() => {
            fetchClients({
                page: 1,
                rows: parseInt(rowsSelect.value, 10),
                search: searchInput.value.trim(),
                bodyId,
                paginationId
            });
        }, 300);

        searchInput.addEventListener('input', fetchTable);
        rowsSelect.addEventListener('change', fetchTable);
    }

    // ---------------------------
    // Initialize
    // ---------------------------
    function initialize() {
        initializeModalFunctionality();
        fetchClients({
            page: 1,
            rows: parseInt(document.getElementById('addressBookRows').value, 10),
            search: document.getElementById('addressBookSearch').value.trim(),
            bodyId: 'addressBookBody',
            paginationId: 'addressBookPagination'
        });
        setupSearchAndRows('addressBookSearch', 'addressBookRows', 'addressBookBody', 'addressBookPagination');
    }

    initialize();
});
