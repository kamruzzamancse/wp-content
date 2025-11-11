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
    // Fetch clients - UPDATED VERSION
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
            setupClientDetailsHandlers(); // Initialize client details handlers
        } else {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;">No clients found</td></tr>`;
        }
    };

    // ---------------------------
    // Client Details Handlers - NEW FUNCTION
    // ---------------------------
    function setupClientDetailsHandlers() {
        console.log('🔧 Setting up client details handlers...');
        
        // Remove existing event listeners to prevent duplicates
        document.removeEventListener('click', handleClientNameClick);
        document.removeEventListener('click', handleViewButtonClick);
        
        // Add new event listeners
        document.addEventListener('click', handleClientNameClick);
        document.addEventListener('click', handleViewButtonClick);
    }

    function handleClientNameClick(e) {
        // Check if clicked on client name cell
        const clientNameCell = e.target.closest('.client-name-cell');
        if (clientNameCell) {
            e.preventDefault();
            console.log('👤 Client name clicked');
            
            const row = clientNameCell.closest('tr');
            const clientId = row.dataset.clientId;
            console.log('📋 Client ID:', clientId);
            
            if (clientId && typeof window.openClientDetailsModal === 'function') {
                window.openClientDetailsModal(clientId);
            } else {
                console.error('❌ Client ID not found or modal function not available');
                console.log('Client ID:', clientId);
                console.log('openClientDetailsModal function:', typeof window.openClientDetailsModal);
            }
        }
    }

    function handleViewButtonClick(e) {
        // Check if clicked on view button
        const viewBtn = e.target.closest('.viewClientBtn');
        if (viewBtn) {
            e.preventDefault();
            const clientId = viewBtn.dataset.clientId;
            console.log('👁️ View button clicked for client:', clientId);
            
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
            const propertyId = document.getElementById('create_realtor_client_property_id').value;
            formData.append('property_id', propertyId);
            formData.append('realtor_client_property_id', propertyId);
            formData.append('action', 'create_realtor_client_ajax');
            formData.append('nonce', rtClientAjax.create_nonce);

            try {
                const response = await fetch(rtClientAjax.ajax_url, { method: 'POST', body: formData });
                const result = await response.json();
                
                if (result.success) {
                    showNotification('Client created successfully! Property ID: ' + result.data.property_id);
                    this.reset();
                    document.getElementById('createRealtorClientPreviewAvatar').src = rtClientAjax.default_avatar;
                    document.getElementById('create_realtor_client_property_id').value = '';
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
                console.error('Submission error:', error);
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Create Client';
            }
        });
    }

    // ---------------------------
    // Property search integration - UPDATED VERSION
    // ---------------------------
    function setupPropertySearch(inputEl, hiddenIdEl, suggestionsEl, nonce) {
        if (!inputEl || !hiddenIdEl || !suggestionsEl) return;

        console.log('🔧 Setting up property search...');
        console.log('📝 Input element:', inputEl);
        console.log('🔑 Hidden ID element:', hiddenIdEl);
        console.log('💡 Suggestions element:', suggestionsEl);

        // Input event: search as user types
        inputEl.addEventListener('input', debounce(function () {
            const keyword = this.value.trim();
            console.log('🔍 Property search input:', keyword);
            
            if (keyword.length < 2) {
                suggestionsEl.style.display = 'none';
                suggestionsEl.innerHTML = '';
                hiddenIdEl.value = ''; // reset ID if user clears input
                console.log('🔄 Input cleared, reset property ID');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'search_properties');
            formData.append('keyword', keyword);
            formData.append('nonce', nonce);

            console.log('📡 Searching properties with keyword:', keyword);

            fetch(rtClientAjax.ajax_url, { method: 'POST', body: formData })
                .then(res => res.json())
                .then(result => {
                    if (result.success && result.data.html) {
                        suggestionsEl.innerHTML = result.data.html;
                        suggestionsEl.style.display = 'block';
                        console.log('✅ Search results found:', suggestionsEl.children.length);
                    } else {
                        suggestionsEl.innerHTML = '<div class="property-suggestion">No results found</div>';
                        suggestionsEl.style.display = 'block';
                        console.log('❌ No search results found');
                    }
                })
                .catch(err => {
                    console.error('❌ Property search error:', err);
                    suggestionsEl.style.display = 'none';
                });
        }, 300));

        // Click on suggestion - UPDATED WITH BETTER DEBUGGING
        suggestionsEl.addEventListener('click', function (e) {
            const suggestion = e.target.closest('.property-suggestion');
            if (suggestion) {
                const id = suggestion.getAttribute('data-id');
                const address = suggestion.textContent;
                
                console.log('🖱️ Property suggestion clicked:');
                console.log('   📍 Address:', address);
                console.log('   🔑 Data ID:', id);
                console.log('   🎯 Hidden element before:', hiddenIdEl);
                console.log('   💾 Hidden value before:', hiddenIdEl.value);

                if (id && !isNaN(id)) {
                    // Set the hidden ID field
                    hiddenIdEl.value = parseInt(id, 10);
                    
                    // Update the input field
                    inputEl.value = address;
                    
                    // Hide suggestions
                    suggestionsEl.style.display = 'none';
                    
                    // Trigger change event to ensure form detects the change
                    hiddenIdEl.dispatchEvent(new Event('change', { bubbles: true }));
                    inputEl.dispatchEvent(new Event('input', { bubbles: true }));
                    
                    console.log('✅ Property selected successfully:');
                    console.log('   📍 Address:', inputEl.value);
                    console.log('   🔑 ID:', hiddenIdEl.value);
                    console.log('   🎯 Hidden element after:', hiddenIdEl);
                    console.log('   💾 Hidden value after:', hiddenIdEl.value);
                    
                    // Additional verification
                    setTimeout(() => {
                        console.log('🔍 Final verification - Hidden ID value:', document.getElementById(hiddenIdEl.id)?.value);
                    }, 100);
                } else {
                    console.error('❌ Invalid property ID:', id);
                }
            }
        });

        // Close suggestions if clicked outside
        document.addEventListener('click', function (e) {
            if (!suggestionsEl.contains(e.target) && e.target !== inputEl) {
                suggestionsEl.style.display = 'none';
                console.log('🚪 Suggestions closed (clicked outside)');
            }
        });

        // Add change event listener to hidden field for debugging
        hiddenIdEl.addEventListener('change', function() {
            console.log('🔄 Hidden property ID changed:', this.value);
            console.log('🔍 Hidden field ID:', this.id);
            console.log('🔍 Hidden field name:', this.getAttribute('name'));
        });

        // Add input event listener to input field for debugging
        inputEl.addEventListener('change', function() {
            console.log('📝 Property input changed:', this.value);
            console.log('🔍 Associated hidden ID:', hiddenIdEl.value);
        });

        // Also monitor form submission for the hidden field
        const form = inputEl.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                console.log('🚀 FORM SUBMISSION DEBUG:');
                console.log('   📝 Property input value:', inputEl.value);
                console.log('   🔑 Hidden ID value:', hiddenIdEl.value);
                console.log('   🆔 Hidden field ID:', hiddenIdEl.id);
                console.log('   📛 Hidden field name:', hiddenIdEl.getAttribute('name'));
                
                // Check if property_id is included in FormData
                const formData = new FormData(form);
                console.log('   📦 FormData entries:');
                for (let [key, value] of formData.entries()) {
                    console.log('      ', key + ':', value);
                }
            });
        }

        console.log('✅ Property search setup completed');
    }

    // Also update the initialization calls with debugging
    console.log('🔧 Initializing property search for edit modal...');

    // Edit Modal Property Search - WITH DEBUGGING
    const editPropertyInput = document.getElementById('edit_realtor_client_property');
    const editPropertyIdInput = document.getElementById('edit_realtor_client_property_id');
    const editSuggestionsBox = document.getElementById('edit_property_suggestions');

    console.log('🔍 Edit modal elements:');
    console.log('   Input:', editPropertyInput);
    console.log('   Hidden ID:', editPropertyIdInput);
    console.log('   Suggestions:', editSuggestionsBox);

    if (editPropertyInput && editPropertyIdInput && editSuggestionsBox) {
        setupPropertySearch(editPropertyInput, editPropertyIdInput, editSuggestionsBox, rtClientAjax.edit_nonce);
    } else {
        console.error('❌ Edit modal property search elements not found!');
    }

    // Create Modal Property Search - WITH DEBUGGING  
    const createPropertyInput = document.getElementById('create_realtor_client_property');
    const createPropertyId = document.getElementById('create_realtor_client_property_id');
    const createSuggestionsBox = document.getElementById('property_suggestions');

    console.log('🔍 Create modal elements:');
    console.log('   Input:', createPropertyInput);
    console.log('   Hidden ID:', createPropertyId);
    console.log('   Suggestions:', createSuggestionsBox);

    if (createPropertyInput && createPropertyId && createSuggestionsBox) {
        setupPropertySearch(createPropertyInput, createPropertyId, createSuggestionsBox, rtClientAjax.create_nonce);
    } else {
        console.error('❌ Create modal property search elements not found!');
    }

    // ---------------------------
    // Edit client
    // ---------------------------
    let isUpdating = false; // Prevent double submission

    async function ajaxFetch(formData) {
        try {
            const response = await fetch(rtClientAjax.ajax_url, {
                method: 'POST',
                body: formData
            });
            return await response.json();
        } catch (err) {
            return { success: false, data: err.message };
        }
    }

    function showNotification(message, type = 'success') {
        document.querySelectorAll('.client-notification').forEach(n => n.remove());
        const notification = document.createElement('div');
        notification.className = `client-notification ${type}`;
        notification.style.cssText = `
            position: fixed; top: 20px; right: 20px; 
            padding: 15px 20px; background: ${type === 'success' ? '#4CAF50' : '#f44336'}; 
            color: white; border-radius: 4px; z-index: 10000;
            font-weight: 500; box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        `;
        notification.textContent = message;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
    }

    const editForm = document.getElementById('editRealtorClientForm');
    if (editForm) {
        editForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const hiddenPropertyId = document.getElementById('edit_realtor_client_property_id').value;

            if (!hiddenPropertyId || hiddenPropertyId === '0') {
                alert('Please select a valid property from the suggestions.');
                return;
            }

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

    // Optional: preview image change in edit modal
    const editProfileInput = document.getElementById('edit_realtor_client_profile_picture');
    const editPreviewAvatar = document.getElementById('editRealtorClientPreviewAvatar');
    if (editProfileInput && editPreviewAvatar) {
        editProfileInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => editPreviewAvatar.src = e.target.result;
                reader.readAsDataURL(file);
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
    // Edit / Delete row buttons - UPDATED VERSION
    // ---------------------------
    function setupRowButtons(bodyId) {
        const tbody = document.getElementById(bodyId);
        if (!tbody) return;

        // Edit button handler - COMPLETELY UPDATED VERSION
        tbody.querySelectorAll('.editClientBtn').forEach(btn => {
            btn.addEventListener('click', async function () {
                const row = this.closest('tr');
                const clientId = row.dataset.clientId;
                if (!clientId) return;

                const modal = document.getElementById('rmRealtorClientEditModal');
                if (!modal) {
                    console.error('Edit modal not found');
                    return;
                }

                modal.style.display = 'flex';

                try {
                    const formData = new FormData();
                    formData.append('action', 'fetch_realtor_client_ajax');
                    formData.append('nonce', rtClientAjax.edit_nonce);
                    formData.append('client_id', clientId);

                    console.log('📡 Fetching client data for editing...');

                    const response = await fetch(rtClientAjax.ajax_url, {
                        method: 'POST',
                        body: formData
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const result = await response.json();

                    if (!result.success) {
                        throw new Error(result.data || 'Failed to fetch client data');
                    }

                    const client = result.data;
                    console.log('📊 Client data loaded for editing:', client);

                    // Fill form fields
                    document.getElementById('edit_realtor_client_id').value = client.client_id;
                    document.getElementById('edit_realtor_client_full_name').value = client.full_name || '';
                    document.getElementById('edit_realtor_client_email').value = client.email || '';
                    document.getElementById('edit_realtor_client_phone').value = client.phone || '';
                    document.getElementById('edit_realtor_client_notes').value = client.note || '';
                    document.getElementById('edit_realtor_client_status').value = client.status || '';

                    // Set profile picture
                    const previewAvatar = document.getElementById('editRealtorClientPreviewAvatar');
                    if (previewAvatar) {
                        previewAvatar.src = client.profile_picture || rtClientAjax.default_avatar;
                    }

                    // Set property fields - WITH DEBUGGING
                    const propertyInput = document.getElementById('edit_realtor_client_property');
                    const propertyIdInput = document.getElementById('edit_realtor_client_property_id');
                    const currentPropertyText = document.getElementById('currentPropertyText');

                    if (propertyInput && propertyIdInput && currentPropertyText) {
                        if (client.property_title && client.property_id) {
                            propertyInput.value = client.property_title;
                            propertyIdInput.value = client.property_id;
                            currentPropertyText.textContent = client.property_title;
                            console.log('🏠 Property set - ID:', client.property_id, 'Title:', client.property_title);
                            console.log('🔍 Property ID Input value after set:', propertyIdInput.value);
                        } else {
                            propertyInput.value = '';
                            propertyIdInput.value = '';
                            currentPropertyText.textContent = 'No property selected';
                            console.log('🏠 No property associated');
                        }
                    }

                    // Show lead status if applicable
                    const leadStatusRow = document.getElementById('leadStatusRow');
                    const leadStatusSelect = document.getElementById('edit_realtor_lead_status');
                    
                    if (leadStatusRow) {
                        leadStatusRow.style.display = (client.status === 'lead') ? 'flex' : 'none';
                    }
                    
                    if (leadStatusSelect && client.lead_status) {
                        leadStatusSelect.value = client.lead_status;
                    }

                    console.log('✅ Edit modal populated successfully');

                } catch (error) {
                    console.error('❌ Error loading client data for editing:', error);
                    alert('Error loading client data: ' + error.message);
                }
            });
        });

        // Delete button handler - UPDATED VERSION
        tbody.querySelectorAll('.deleteClientBtn').forEach(btn => {
            btn.addEventListener('click', async function () {
                const row = this.closest('tr');
                const clientId = row.dataset.clientId;
                if (!clientId) return;
                
                if (!confirm('Are you sure you want to delete this client? This action cannot be undone.')) {
                    return;
                }

                const deleteBtn = this;
                const originalText = deleteBtn.textContent;
                deleteBtn.disabled = true;
                deleteBtn.textContent = 'Deleting...';

                try {
                    const formData = new FormData();
                    formData.append('action', 'delete_realtor_client_ajax');
                    formData.append('nonce', rtClientAjax.delete_nonce);
                    formData.append('client_id', clientId);

                    console.log('🗑️ Deleting client:', clientId);

                    const response = await fetch(rtClientAjax.ajax_url, {
                        method: 'POST',
                        body: formData
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const result = await response.json();

                    if (result.success) {
                        showNotification('Client deleted successfully!', 'success');
                        console.log('✅ Client deleted successfully');

                        // Refresh the table
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
                    console.error('❌ Error deleting client:', error);
                    showNotification('Error deleting client: ' + error.message, 'error');
                } finally {
                    deleteBtn.disabled = false;
                    deleteBtn.textContent = originalText;
                }
            });
        });

        // Also add event delegation for dynamic rows
        tbody.addEventListener('click', function(e) {
            // Handle edit buttons
            if (e.target.classList.contains('editClientBtn') || e.target.closest('.editClientBtn')) {
                const btn = e.target.classList.contains('editClientBtn') ? e.target : e.target.closest('.editClientBtn');
                const row = btn.closest('tr');
                const clientId = row.dataset.clientId;
                
                if (clientId) {
                    // Trigger the edit button click programmatically
                    btn.click();
                }
            }

            // Handle delete buttons
            if (e.target.classList.contains('deleteClientBtn') || e.target.closest('.deleteClientBtn')) {
                const btn = e.target.classList.contains('deleteClientBtn') ? e.target : e.target.closest('.deleteClientBtn');
                const row = btn.closest('tr');
                const clientId = row.dataset.clientId;
                
                if (clientId) {
                    // Trigger the delete button click programmatically
                    btn.click();
                }
            }
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
