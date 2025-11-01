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
                    <td class="client-name-text">${client.full_name}</td>
                    <td>${client.email}</td>
                    <td>${client.phone || ''}</td>
                    <td>${client.note || ''}</td>
                    <td>${client.status || ''}</td>
                    <td>
                        <span class="editClientBtn" style="cursor:pointer;">✏️</span>
                        <span class="deleteClientBtn" style="cursor:pointer;">🗑️</span>
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
        } else {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;">No clients found</td></tr>`;
        }
    };

    // ---------------------------
    // Create client - UPDATED VERSION
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
            
            // Explicitly add properties_id to ensure it's sent
            const propertyId = document.getElementById('create_realtor_client_property_id').value;
            formData.append('properties_id', propertyId);
            formData.append('realtor_client_property_id', propertyId);
            
            formData.append('action', 'create_realtor_client_ajax');
            formData.append('nonce', rtClientAjax.create_nonce);

            // Debug: log form data
            console.log('=== FORM SUBMISSION DATA ===');
            for (let [key, value] of formData.entries()) {
                console.log(key + ': ' + value);
            }

            try {
                const response = await fetch(rtClientAjax.ajax_url, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification('Client created successfully! Property ID: ' + result.data.property_id);
                    this.reset();
                    document.getElementById('createRealtorClientPreviewAvatar').src = rtClientAjax.default_avatar;
                    document.getElementById('create_realtor_client_property_id').value = '';
                    document.getElementById('rmRealtorClientCreateModal').style.display = 'none';
                    
                    // Refresh clients table
                    setTimeout(() => {
                        if(typeof fetchClients === 'function') {
                            fetchClients({
                                page: 1,
                                rows: parseInt(document.getElementById('addressBookRows').value, 10),
                                search: document.getElementById('addressBookSearch').value.trim(),
                                bodyId: 'addressBookBody',
                                paginationId: 'addressBookPagination'
                            });
                        }
                    }, 500);
                } else {
                    showNotification('Error: ' + result.data, 'error');
                }
            } catch (error) {
                showNotification('Network error. Please try again.', 'error');
                console.error('Submission error:', error);
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Create Client';
            }
        });
    }

    // ---------------------------
    // Property search integration for Create modal ONLY
    // ---------------------------
    function setupPropertySearch(inputEl, hiddenIdEl, suggestionsEl, nonce) {
        function debounce(func, delay = 300) {
            let timer;
            return function (...args) {
                clearTimeout(timer);
                timer = setTimeout(() => func.apply(this, args), delay);
            };
        }

        // Keyup: fetch suggestions
        inputEl.addEventListener('input', debounce(function () {
            const keyword = this.value.trim();
            if (keyword.length < 2) {
                suggestionsEl.style.display = 'none';
                return;
            }

            const data = new FormData();
            data.append('action', 'search_properties');
            data.append('keyword', keyword);
            data.append('nonce', nonce);

            fetch(rtClientAjax.ajax_url, {
                method: 'POST',
                body: data
            })
            .then(res => res.json())
            .then(result => {
                if (result.success && result.data.html) {
                    suggestionsEl.innerHTML = result.data.html;
                    suggestionsEl.style.display = 'block';
                } else {
                    suggestionsEl.innerHTML = '<div class="property-suggestion">No results found</div>';
                    suggestionsEl.style.display = 'block';
                }
            });
        }, 300));

        // Click on suggestion
        suggestionsEl.addEventListener('click', function (e) {
            if (e.target && e.target.classList.contains('property-suggestion')) {
                inputEl.value = e.target.textContent;
                hiddenIdEl.value = e.target.dataset.id;
                suggestionsEl.style.display = 'none';
                console.log('Property selected - ID:', e.target.dataset.id, 'Address:', e.target.textContent);
            }
        });

        // Hide suggestions on click outside
        document.addEventListener('click', function (e) {
            if (!suggestionsEl.contains(e.target) && e.target !== inputEl) {
                suggestionsEl.style.display = 'none';
            }
        });
    }

    // Initialize Create modal property search
    const createPropertyInput = document.getElementById('create_realtor_client_property');
    const createPropertyId = document.getElementById('create_realtor_client_property_id');
    const createSuggestionsBox = document.getElementById('property_suggestions');

    if (createPropertyInput && createPropertyId && createSuggestionsBox) {
        setupPropertySearch(createPropertyInput, createPropertyId, createSuggestionsBox, rtClientAjax.create_nonce);
    }

    // ---------------------------
    // Edit client
    // ---------------------------
    const editForm = document.getElementById('editRealtorClientForm');
    if (editForm) {
        editForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            if (isProcessing) return;
            isProcessing = true;

            const submitBtn = editForm.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Updating...';

            const formData = new FormData(editForm);
            formData.append('action', 'update_realtor_client_ajax');
            formData.append('nonce', rtClientAjax.edit_nonce);

            try {
                const result = await ajaxFetch(formData);
                if (result.success) {
                    showNotification('Client updated successfully!');
                    document.getElementById('rmRealtorClientEditModal').style.display = 'none';
                    await fetchClients({
                        page: 1,
                        rows: parseInt(document.getElementById('addressBookRows').value, 10),
                        search: document.getElementById('addressBookSearch').value.trim(),
                        bodyId: 'addressBookBody',
                        paginationId: 'addressBookPagination'
                    });
                } else {
                    showNotification('Error: ' + result.data, 'error');
                }
            } catch (error) {
                showNotification('Network error. Please try again.', 'error');
            } finally {
                isProcessing = false;
                submitBtn.disabled = false;
                submitBtn.textContent = 'Update Client';
            }
        });
    }

    // ---------------------------
    // Modal functionality (Create/Edit)
    // ---------------------------
    function initializeModalFunctionality() {
        const createModal = document.getElementById('rmRealtorClientCreateModal');
        const addContactBtn = document.querySelector('.ab-btn-create');
        const closeBtn = document.getElementById('closeRealtorClientCreateModal');
        const profileInput = document.getElementById('create_realtor_client_profile_picture');
        const previewAvatar = document.getElementById('createRealtorClientPreviewAvatar');

        if (addContactBtn && createModal) addContactBtn.addEventListener('click', () => createModal.style.display = 'flex');
        if (closeBtn && createModal) {
            closeBtn.addEventListener('click', () => createModal.style.display = 'none');
            createModal.addEventListener('click', e => { if (e.target === createModal) createModal.style.display = 'none'; });
        }
        if (profileInput && previewAvatar) {
            profileInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => previewAvatar.src = e.target.result;
                    reader.readAsDataURL(file);
                }
            });
        }

        const editProfileInput = document.getElementById('edit_realtor_client_profile_picture');
        const editPreviewAvatar = document.getElementById('editRealtorClientPreviewAvatar');
        if (editProfileInput && editPreviewAvatar) {
            editProfileInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => editPreviewAvatar.src = e.target.result;
                    reader.readAsDataURL(file);
                }
            });
        }
    }

    // ---------------------------
    // Edit / Delete row buttons
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
                modal.style.display = 'flex';

                const formData = new FormData();
                formData.append('action', 'fetch_realtor_client_ajax');
                formData.append('nonce', rtClientAjax.edit_nonce);
                formData.append('client_id', clientId);

                const result = await ajaxFetch(formData);

                // When filling edit modal with client info
                if(result.success){
                    const client = result.data;
                    document.getElementById('edit_realtor_client_id').value = client.client_id;
                    document.getElementById('edit_realtor_client_full_name').value = client.full_name;
                    document.getElementById('edit_realtor_client_email').value = client.email;
                    document.getElementById('edit_realtor_client_phone').value = client.phone;
                    document.getElementById('edit_realtor_client_notes').value = client.note;
                    document.getElementById('edit_realtor_client_status').value = client.status;
                    document.getElementById('editRealtorClientPreviewAvatar').src = client.profile_picture || rtClientAjax.default_avatar;
                    
                    // Property info
                    document.getElementById('edit_realtor_client_property').value = '';
                    document.getElementById('edit_realtor_client_property_id').value = client.property_id || '';
                    document.getElementById('previousPropertyName').textContent = client.property_title || 'No property selected';
                }
            });
        });

        tbody.querySelectorAll('.deleteClientBtn').forEach(btn => {
            btn.addEventListener('click', async function () {
                const row = this.closest('tr');
                const clientId = row.dataset.clientId;
                if (!clientId) return;
                if (!confirm('Are you sure you want to delete this client?')) return;

                const formData = new FormData();
                formData.append('action', 'delete_realtor_client_ajax');
                formData.append('nonce', rtClientAjax.delete_nonce);
                formData.append('client_id', clientId);

                const result = await ajaxFetch(formData);

                if (result.success) {
                    showNotification('Client deleted successfully!');
                    await fetchClients({
                        page: 1,
                        rows: parseInt(document.getElementById('addressBookRows').value, 10),
                        search: document.getElementById('addressBookSearch').value.trim(),
                        bodyId: 'addressBookBody',
                        paginationId: 'addressBookPagination'
                    });
                } else {
                    showNotification('Error: ' + result.data, 'error');
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
    // Export Clients
    // ---------------------------
    const exportModal = document.getElementById('abExportModal');
    const exportStatus = document.getElementById('abExportStatus');
    const exportBtn = document.getElementById('abExportStart');

    if (exportBtn) {
        exportBtn.addEventListener('click', async () => {
            exportBtn.disabled = true;
            exportBtn.textContent = 'Exporting...';

            const format = document.querySelector('input[name="ab_export_format"]:checked')?.value || 'csv';
            const scope = document.querySelector('input[name="ab_export_scope"]:checked')?.value || 'current';
            const columns = Array.from(document.querySelectorAll('input[name="ab_export_columns"]:checked')).map(el => el.value);
            const currentIds = Array.from(document.querySelectorAll('#addressBookBody tr')).map(tr => parseInt(tr.dataset.clientId));

            exportStatus.textContent = 'Exporting...';

            try {
                const formData = new FormData();
                formData.append('action', 'export_clients_ajax');
                formData.append('nonce', rtClientAjax.export_nonce);
                formData.append('format', format);
                formData.append('scope', scope);
                formData.append('columns', JSON.stringify(columns));
                if (scope === 'current') formData.append('current_ids', JSON.stringify(currentIds));

                const response = await fetch(rtClientAjax.ajax_url, { method: 'POST', body: formData });
                if (!response.ok) throw new Error('Network response not ok');

                const data = await response.json();
                if (!data.success) throw new Error(data.data || 'Export failed');

                const clients = data.data.clients || [];
                if (!clients.length) throw new Error('No clients found.');

                if (format === 'csv') {
                    const csvRows = [];
                    csvRows.push(columns.join(','));
                    clients.forEach(client => {
                        const row = columns.map(col => {
                            let val = client[col] ?? '';
                            if (typeof val === 'string' && val.includes(',')) val = `"${val.replace(/"/g, '""')}"`;
                            return val;
                        });
                        csvRows.push(row.join(','));
                    });
                    const blob = new Blob([csvRows.join('\n')], { type: 'text/csv;charset=utf-8;' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `clients-export-${new Date().toISOString().slice(0,10)}.csv`;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    URL.revokeObjectURL(url);
                } else if (format === 'xlsx') {
                    if (typeof XLSX === 'undefined') throw new Error('XLSX library not loaded');
                    const worksheetData = clients.map(client => {
                        const obj = {};
                        columns.forEach(col => obj[col] = client[col] ?? '');
                        return obj;
                    });
                    const ws = XLSX.utils.json_to_sheet(worksheetData);
                    const wb = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(wb, ws, 'Clients');
                    XLSX.writeFile(wb, `clients-export-${new Date().toISOString().slice(0,10)}.xlsx`);
                }

                exportStatus.textContent = 'Export completed!';
                setTimeout(() => exportModal.style.display = 'none', 1000);
            } catch (error) {
                exportStatus.textContent = 'Export failed: ' + error.message;
            } finally {
                exportBtn.disabled = false;
                exportBtn.textContent = 'Start Export';
            }
        });
    }

    // ---------------------------
    // Import Clients
    // ---------------------------
    const importInput = document.getElementById('abImportFileInput');
    const importBtn = document.getElementById('abImportStart');
    const importStatus = document.getElementById('abImportStatus');

    if (importInput && importBtn) {
        importBtn.addEventListener('click', async () => {
            if (!importInput.files || !importInput.files[0]) {
                importStatus.textContent = 'Please select a file to import.';
                return;
            }

            importBtn.disabled = true;
            importBtn.textContent = 'Importing...';
            importStatus.textContent = 'Importing...';

            const file = importInput.files[0];
            const duplicateHandling = document.querySelector('input[name="ab_import_duplicate"]:checked')?.value || 'skip';

            const formData = new FormData();
            formData.append('action', 'import_clients_ajax');
            formData.append('nonce', rtClientAjax.import_nonce);
            formData.append('clients_file', file);
            formData.append('duplicate_handling', duplicateHandling);

            try {
                const response = await fetch(rtClientAjax.ajax_url, { method: 'POST', body: formData });
                if (!response.ok) throw new Error('Network response not ok');

                const result = await response.json();

                if (result.success) {
                    const message = result.data?.message || 'Clients imported successfully!';
                    importStatus.textContent = message;

                    if (typeof window.fetchClients === 'function') {
                        await window.fetchClients({
                            page: 1,
                            rows: parseInt(document.getElementById('addressBookRows').value, 10),
                            search: document.getElementById('addressBookSearch').value.trim(),
                            bodyId: 'addressBookBody',
                            paginationId: 'addressBookPagination'
                        });
                    }

                    importInput.value = '';
                    importBtn.disabled = true;
                    importBtn.textContent = 'Import';
                    setTimeout(() => document.getElementById('abImportModal').style.display = 'none', 1000);
                } else {
                    const errMsg = result.data?.message || result.data || 'Unknown error';
                    importStatus.textContent = 'Import failed: ' + errMsg;
                }

            } catch (error) {
                importStatus.textContent = 'Import failed: ' + error.message;
            } finally {
                importBtn.disabled = false;
                importBtn.textContent = 'Import';
            }
        });
    }

    // ---------------------------
    // Client Details modal
    // ---------------------------
    const tbody = document.getElementById('addressBookBody');
    const clientModal = document.getElementById('clientDetailsModal');
    const closeClientBtn = document.getElementById('closeClientDetailsModal');

    if (clientModal && closeClientBtn) {
        closeClientBtn.addEventListener('click', () => clientModal.style.display = 'none');
        clientModal.addEventListener('click', e => { if (e.target === clientModal) clientModal.style.display = 'none'; });
    }

    if (tbody) {
        tbody.addEventListener('click', async function(e) {
            const target = e.target.closest('.client-name-text');
            if (!target) return;

            const row = target.closest('tr');
            const clientId = row.dataset.clientId;
            if (!clientId) return;

            const formData = new FormData();
            formData.append('action', 'fetch_realtor_client_ajax');
            formData.append('nonce', rtClientAjax.edit_nonce);
            formData.append('client_id', clientId);

            const result = await ajaxFetch(formData);

            if (!result.success) {
                showNotification('Error fetching client details: ' + result.data, 'error');
                return;
            }

            const client = result.data;
            clientModal.querySelector('#clientAvatar').src = client.profile_picture || rtClientAjax.default_avatar;
            clientModal.querySelector('#clientName').textContent = client.full_name || '';
            clientModal.querySelector('#clientCompany').textContent = client.company_name || '';
            clientModal.querySelector('#clientNameCell').textContent = client.full_name || '';
            clientModal.querySelector('#clientEmailCell').textContent = client.email || '';
            clientModal.querySelector('#clientPhoneCell').textContent = client.phone || '';
            clientModal.querySelector('#clientNotesCell').textContent = client.note || '';
            clientModal.querySelector('#clientDobCell').textContent = client.date_of_birth || '';
            clientModal.querySelector('#clientHouseClosingCell').textContent = client.house_closing_date || '';

            clientModal.querySelector('#clientPropertyImage').src = client.property_image || rtClientAjax.default_property_image;
            clientModal.querySelector('#clientPropertyTitle').textContent = client.property_title || '';
            clientModal.querySelector('#clientPropertyPrice').textContent = client.property_price || '';
            clientModal.querySelector('#clientPropertyLocation').textContent = client.property_location || '';

            const gallery = clientModal.querySelector('#clientPropertyGallery');
            gallery.innerHTML = '';
            if (client.property_gallery && client.property_gallery.length) {
                client.property_gallery.forEach(img => {
                    const image = document.createElement('img');
                    image.src = img;
                    gallery.appendChild(image);
                });
            }

            clientModal.style.display = 'flex';
        });
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
