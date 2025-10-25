document.addEventListener('DOMContentLoaded', function () {

    // ---------------------------
    // AJAX fetch wrapper
    // ---------------------------
    async function ajaxFetch(formData) {
        try {
            const response = await fetch(rtClientAjax.ajax_url, {
                method: 'POST',
                body: formData
            });
            return await response.json();
        } catch (error) {
            alert('Network error: ' + error.message);
            return { success: false, data: error.message };
        }
    }

    // ---------------------------
    // Debounce helper
    // ---------------------------
    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // ---------------------------
    // Fetch clients
    // ---------------------------
    async function fetchClients({ page = 1, rows = 10, search = '', bodyId, paginationId }) {
        const formData = new FormData();
        formData.append('action', 'fetch_clients_ajax');
        formData.append('nonce', rtClientAjax.edit_nonce);
        formData.append('page', page);
        formData.append('rows', rows);
        formData.append('search', search);

        const result = await ajaxFetch(formData);
        const tbody = document.getElementById(bodyId);
        const paginationContainer = document.getElementById(paginationId);
        if (!tbody || !paginationContainer) return;

        tbody.innerHTML = '';
        paginationContainer.innerHTML = '';

        if (result.success && result.data.clients.length > 0) {
            result.data.clients.forEach(client => {
                const tr = document.createElement('tr');
                tr.dataset.clientId = client.client_id;
                tr.innerHTML = `
                    <td><img src="${client.profile_picture || rtClientAjax.default_avatar}" style="width:40px;height:40px;border-radius:50%;object-fit:cover;"></td>
                    <td class="client-name-text" style="cursor:pointer;color:#0052cc;">${client.full_name}</td>
                    <td>${client.email}</td>
                    <td>${client.phone || ''}</td>
                    <td>${client.note || ''}</td>
                    <td>${client.status || ''}</td>
                    <td class="action-cell">
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
                paginationContainer.appendChild(btn);
            }

            // Setup buttons
            setupRowButtons(bodyId);
        } else {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;">No clients found</td></tr>`;
        }
    }

    // ---------------------------
    // Setup Edit / Delete / View
    // ---------------------------
    function setupRowButtons(bodyId) {
        const tbody = document.getElementById(bodyId);
        if (!tbody) return;

        // Remove previous listeners
        tbody.querySelectorAll('.editClientBtn').forEach(btn => btn.replaceWith(btn.cloneNode(true)));
        tbody.querySelectorAll('.deleteClientBtn').forEach(btn => btn.replaceWith(btn.cloneNode(true)));
        tbody.querySelectorAll('.client-name-text').forEach(btn => btn.replaceWith(btn.cloneNode(true)));

        // Edit
        tbody.querySelectorAll('.editClientBtn').forEach(btn => {
            btn.addEventListener('click', async function () {
                const row = this.closest('tr');
                const clientId = row.dataset.clientId;
                if (!clientId) return alert('Missing client ID');

                const modal = document.getElementById('rmRealtorClientEditModal');
                if (!modal) return;

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

        // Delete
        tbody.querySelectorAll('.deleteClientBtn').forEach(btn => {
            btn.addEventListener('click', async function () {
                const row = this.closest('tr');
                const clientId = row.dataset.clientId;
                if (!clientId) return alert('Missing client ID');
                if (!confirm('Are you sure you want to delete this client?')) return;

                const formData = new FormData();
                formData.append('action', 'delete_realtor_client_ajax');
                formData.append('nonce', rtClientAjax.delete_nonce);
                formData.append('client_id', clientId);

                const result = await ajaxFetch(formData);
                if (result.success) {
                    row.remove();
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
    // Initialize tables
    // ---------------------------
    fetchClients({ page: 1, rows: 10, search: '', bodyId: 'addressBookBody', paginationId: 'addressBookPagination' });
    fetchClients({ page: 1, rows: 10, search: '', bodyId: 'activeClientsBody', paginationId: 'activeClientsPagination' });

    setupSearchAndRows('addressBookSearch', 'addressBookRows', 'addressBookBody', 'addressBookPagination');
    setupSearchAndRows('activeClientsSearch', 'activeClientsRows', 'activeClientsBody', 'activeClientsPagination');

});
