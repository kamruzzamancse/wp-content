document.addEventListener('DOMContentLoaded', function () {
    // ---------------------------
    // AJAX wrapper
    // ---------------------------
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
    // Fetch clients - Global function banaye din
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

            // Pagination
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
    }

    // ---------------------------
    // Create client - Improved version
    // ---------------------------
    const createForm = document.getElementById('createRealtorClientForm');
    if (createForm) {
        // Remove any existing listeners
        createForm.replaceWith(createForm.cloneNode(true));
        const freshForm = document.getElementById('createRealtorClientForm');
        
        freshForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const submitBtn = freshForm.querySelector('button[type="submit"]');
            
            // Disable button to prevent double submission
            if (submitBtn.disabled) return;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating...';

            const formData = new FormData(freshForm);
            formData.append('action', 'create_realtor_client_ajax');
            formData.append('nonce', rtClientAjax.create_nonce);

            try {
                const response = await fetch(rtClientAjax.ajax_url, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();

                if (result.success) {
                    alert('Client created successfully!');
                    
                    // Form reset
                    freshForm.reset();
                    document.getElementById('createRealtorClientPreviewAvatar').src = rtClientAjax.default_avatar;
                    document.getElementById('rmRealtorClientCreateModal').style.display = 'none';
                    
                    // Refresh the address book table
                    await fetchClients({
                        page: 1,
                        rows: parseInt(document.getElementById('addressBookRows').value, 10),
                        search: document.getElementById('addressBookSearch').value.trim(),
                        bodyId: 'addressBookBody',
                        paginationId: 'addressBookPagination'
                    });
                    
                } else {
                    alert('Error: ' + result.data);
                }
            } catch (err) {
                alert('Network error: ' + err.message);
            } finally {
                // Re-enable button
                submitBtn.disabled = false;
                submitBtn.textContent = 'Create Client';
            }
        });
    }

    // ---------------------------
    // Modal open/close functionality
    // ---------------------------
    function initializeModalFunctionality() {
        const createModal = document.getElementById('rmRealtorClientCreateModal');
        const addContactBtn = document.querySelector('.ab-btn-create');
        const closeBtn = document.getElementById('closeRealtorClientCreateModal');
        const profileInput = document.getElementById('create_realtor_client_profile_picture');
        const previewAvatar = document.getElementById('createRealtorClientPreviewAvatar');

        // Open modal
        if (addContactBtn && createModal) {
            addContactBtn.addEventListener('click', () => {
                createModal.style.display = 'flex';
            });
        }

        // Close modal
        if (closeBtn && createModal) {
            closeBtn.addEventListener('click', () => {
                createModal.style.display = 'none';
            });
            createModal.addEventListener('click', (e) => {
                if (e.target === createModal) {
                    createModal.style.display = 'none';
                }
            });
        }

        // Image preview
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
    }

    // ---------------------------
    // Edit / Delete row buttons
    // ---------------------------
    function setupRowButtons(bodyId) {
        const tbody = document.getElementById(bodyId);
        if (!tbody) return;

        // Remove previous listeners by cloning
        tbody.querySelectorAll('.editClientBtn, .deleteClientBtn').forEach(btn => {
            btn.replaceWith(btn.cloneNode(true));
        });

        // Edit functionality
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
                if (result.success) {
                    const client = result.data;
                    document.getElementById('edit_realtor_client_id').value = client.client_id;
                    document.getElementById('edit_realtor_client_full_name').value = client.full_name;
                    document.getElementById('edit_realtor_client_email').value = client.email;
                    document.getElementById('edit_realtor_client_phone').value = client.phone;
                    document.getElementById('edit_realtor_client_notes').value = client.note;
                    document.getElementById('edit_realtor_client_status').value = client.status;
                    document.getElementById('editRealtorClientPreviewAvatar').src = client.profile_picture || rtClientAjax.default_avatar;
                } else {
                    alert('Error: ' + result.data);
                    modal.style.display = 'none';
                }
            });
        });

        // Delete functionality
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
                    // Refresh table after delete
                    await fetchClients({ 
                        page: 1,
                        rows: parseInt(document.getElementById('addressBookRows').value, 10),
                        search: document.getElementById('addressBookSearch').value.trim(),
                        bodyId: 'addressBookBody',
                        paginationId: 'addressBookPagination'
                    });
                } else {
                    alert('Error: ' + result.data);
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
    // Initialize everything
    // ---------------------------
    function initialize() {
        // Initialize modal functionality
        initializeModalFunctionality();
        
        // Load initial data
        fetchClients({ 
            page: 1, 
            rows: parseInt(document.getElementById('addressBookRows').value, 10),
            search: document.getElementById('addressBookSearch').value.trim(),
            bodyId: 'addressBookBody', 
            paginationId: 'addressBookPagination'
        });

        // Setup search and rows
        setupSearchAndRows('addressBookSearch', 'addressBookRows', 'addressBookBody', 'addressBookPagination');
    }

    // Start the application
    initialize();
});