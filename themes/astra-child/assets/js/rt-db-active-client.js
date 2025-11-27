document.addEventListener('DOMContentLoaded', function () {

    const ajaxUrl = rtActiveClientAjax.ajax_url;
    const paginationNonce = rtActiveClientAjax.pagination_nonce;
    const createClientNonce = rtActiveClientAjax.create_client_nonce;
    const editClientNonce = rtActiveClientAjax.edit_client_nonce;
    const deleteClientNonce = rtActiveClientAjax.delete_client_nonce;
    const defaultAvatar = rtActiveClientAjax.default_avatar;

    // --------------------------
    // AJAX Helper
    // --------------------------
    async function ajaxFetch(formData) {
        try {
            const res = await fetch(ajaxUrl, { method: 'POST', body: formData });
            return await res.json();
        } catch (err) {
            alert("Network error: " + err.message);
            return { success: false };
        }
    }

    // --------------------------
    // FETCH CLIENTS
    // --------------------------
    async function fetchClients({ page = 1, rows = 10, search = '' }) {
        const fd = new FormData();
        fd.append('action', 'fetch_dashboard_active_clients_ajax');
        fd.append('nonce', paginationNonce);
        fd.append('page', page);
        fd.append('rows', rows);
        fd.append('search', search);

        const data = await ajaxFetch(fd);
        const tbody = document.getElementById('activeClientsBody');
        const pagination = document.getElementById('activeClientsPagination');
        if (!tbody || !pagination) return;

        tbody.innerHTML = '';
        pagination.innerHTML = '';

        if (data.success && data.data.clients.length > 0) {
            data.data.clients.forEach(client => {
                const tr = document.createElement('tr');
                tr.dataset.clientId = client.client_id;

                tr.innerHTML = `
                    <td>${client.full_name || ''}</td>
                    <td>${client.email || ''}</td>
                    <td>${client.phone || ''}</td>
                    <td>${client.note || ''}</td>
                    <td>
                        <span class="editClientBtn" style="cursor:pointer;color:#0052cc;">✏️</span>
                        <span class="deleteClientBtn" style="cursor:pointer;color:red;margin-left:5px;">🗑️</span>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            // Pagination buttons
            for (let i = 1; i <= data.data.total_pages; i++) {
                const btn = document.createElement('button');
                btn.textContent = i;
                if (i === page) btn.classList.add('active');
                btn.addEventListener('click', () => fetchClients({ page: i, rows, search }));
                pagination.appendChild(btn);
            }

            setupRowButtons();
        } else {
            tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;">No active clients found</td></tr>`;
        }
    }

    // --------------------------
    // EDIT / DELETE BUTTONS
    // --------------------------
    function setupRowButtons() {
        const tbody = document.getElementById('activeClientsBody');
        if (!tbody) return;

        // Remove previous listeners
        tbody.querySelectorAll('.editClientBtn, .deleteClientBtn')
            .forEach(btn => btn.replaceWith(btn.cloneNode(true)));

        // EDIT
        tbody.querySelectorAll('.editClientBtn').forEach(btn => {
            btn.addEventListener('click', async function () {
                const row = this.closest('tr');
                const clientId = row.dataset.clientId;
                const modal = document.getElementById('rtDbClientEditModal');
                if (!modal) { alert('Edit modal not found!'); return; }

                const fd = new FormData();
                fd.append('action', 'fetch_dashboard_client_ajax');
                fd.append('nonce', editClientNonce);
                fd.append('client_id', clientId);

                const result = await ajaxFetch(fd);

                if (result.success) {
                    const client = result.data;

                    document.getElementById('edit_rt_db_client_id').value = client.client_id || '';
                    document.getElementById('edit_rt_db_client_full_name').value = client.full_name || '';
                    document.getElementById('edit_rt_db_client_email').value = client.email || '';
                    document.getElementById('edit_rt_db_client_phone').value = client.phone || '';
                    document.getElementById('edit_rt_db_client_address').value = client.address || '';
                    document.getElementById('edit_rt_db_client_note').value = client.note || '';

                    const avatar = document.getElementById('editRtDbClientPreviewAvatar');
                    avatar.src = client.profile_picture && client.profile_picture.trim() !== '' ? client.profile_picture : defaultAvatar;

                    modal.style.display = 'flex'; // <-- This opens the modal
                } else {
                    alert('Error fetching client!');
                }
            });

        });

        // DELETE
        tbody.querySelectorAll('.deleteClientBtn').forEach(btn => {
            btn.addEventListener('click', async function () {
                const row = this.closest('tr');
                const clientId = row.dataset.clientId;
                if (!confirm('Are you sure you want to delete this client?')) return;

                const fd = new FormData();
                fd.append('action', 'delete_dashboard_client_ajax');
                fd.append('nonce', deleteClientNonce);
                fd.append('client_id', clientId);

                const result = await ajaxFetch(fd);
                if (result.success) {
                    alert('Client deleted successfully!');
                    fetchClients({
                        page: 1,
                        rows: parseInt(document.getElementById('activeClientsRows').value),
                        search: document.getElementById('activeClientsSearch').value
                    });
                } else {
                    alert('Error deleting client!');
                }
            });
        });
    }

    // --------------------------
    // SEARCH & ROWS
    // --------------------------
    function setupSearchAndRows() {
        const searchInput = document.getElementById('activeClientsSearch');
        const rowsSelect = document.getElementById('activeClientsRows');
        if (!searchInput || !rowsSelect) return;

        function debounce(fn, delay) {
            let timer;
            return function (...args) {
                clearTimeout(timer);
                timer = setTimeout(() => fn.apply(this, args), delay);
            };
        }

        const fetchTable = debounce(() => {
            fetchClients({
                page: 1,
                rows: parseInt(rowsSelect.value),
                search: searchInput.value
            });
        }, 300);

        searchInput.addEventListener('input', fetchTable);
        rowsSelect.addEventListener('change', fetchTable);
    }

    // --------------------------
    // CREATE CLIENT
    // --------------------------
    const createModal = document.getElementById('rtDbClientCreateModal');
    const addActiveBtn = document.getElementById('addActiveBtn');
    const closeCreateBtn = document.getElementById('closeRtDbClientCreateModal');
    const createForm = document.getElementById('createRtDbClientForm');
    const createProfileInput = document.getElementById('create_rt_db_client_profile_picture');
    const createPreviewAvatar = document.getElementById('createRtDbClientPreviewAvatar');

    if (addActiveBtn) addActiveBtn.addEventListener('click', () => createModal.style.display = 'flex');
    if (closeCreateBtn) closeCreateBtn.addEventListener('click', () => createModal.style.display = 'none');
    if (createModal) createModal.addEventListener('click', e => { if (e.target === createModal) createModal.style.display = 'none'; });

    if (createProfileInput) {
        createProfileInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = e => createPreviewAvatar.src = e.target.result;
            reader.readAsDataURL(file);
        });
    }

    if (createForm) {
        createForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const fd = new FormData(this);
            fd.append('action', 'create_dashboard_client_ajax');
            fd.append('nonce', createClientNonce);

            const result = await ajaxFetch(fd);
            if (result.success) {
                alert('Client created successfully!');
                createForm.reset();
                createPreviewAvatar.src = defaultAvatar;
                createModal.style.display = 'none';
                fetchClients({
                    page: 1,
                    rows: parseInt(document.getElementById('activeClientsRows').value),
                    search: document.getElementById('activeClientsSearch').value
                });
            } else {
                alert('Error creating client!');
            }
        });
    }

    // --------------------------
    // EDIT CLIENT
    // --------------------------
    const editForm = document.getElementById('editRtDbClientForm');
    const editProfileInput = document.getElementById('edit_rt_db_client_profile_picture');

    if (editProfileInput) {
        editProfileInput.addEventListener('change', function () {
            const f = this.files[0];
            if (!f) return;
            const r = new FileReader();
            r.onload = e => document.getElementById('editRtDbClientPreviewAvatar').src = e.target.result;
            r.readAsDataURL(f);
        });
    }

    if (editForm) {
        editForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const fd = new FormData(editForm);
            fd.append('action', 'update_dashboard_client_ajax');
            fd.append('nonce', editClientNonce);

            const result = await ajaxFetch(fd);

            if (result.success) {
                const updatedClient = result.data;

                // Update table row
                const row = document.querySelector(`tr[data-client-id='${updatedClient.client_id}']`);
                if (row) {
                    row.innerHTML = `
                        <td>${updatedClient.full_name || ''}</td>
                        <td>${updatedClient.email || ''}</td>
                        <td>${updatedClient.phone || ''}</td>
                        <td>${updatedClient.note || ''}</td>
                        <td>
                            <span class="editClientBtn" style="cursor:pointer;color:#0052cc;">✏️</span>
                            <span class="deleteClientBtn" style="cursor:pointer;color:red;margin-left:5px;">🗑️</span>
                        </td>
                    `;
                }

                setupRowButtons(); // Reattach edit/delete listeners
                alert('Client updated successfully!');
                document.getElementById('rtDbClientEditModal').style.display = 'none';
            } else {
                alert('Error updating client!');
            }
        });
    }

    // --------------------------
    // INITIAL LOAD
    // --------------------------
    fetchClients({
        page: 1,
        rows: parseInt(document.getElementById('activeClientsRows').value || 10),
        search: document.getElementById('activeClientsSearch').value || ''
    });

    setupSearchAndRows();

});
