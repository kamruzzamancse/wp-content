document.addEventListener('DOMContentLoaded', function () {
    let isProcessing = false;

    // ---------------------------
    // AJAX wrapper
    // ---------------------------
    async function ajaxFetch(formData) {
        try {
            const response = await fetch(rtRealtorAjax.ajax_url, { method: 'POST', body: formData });
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
        document.querySelectorAll('.realtor-notification').forEach(n => n.remove());
        const notification = document.createElement('div');
        notification.className = `realtor-notification ${type}`;
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
    // Fetch Realtors
    // ---------------------------
    window.fetchRealtors = async function({ page = 1, rows = 10, search = '', bodyId, paginationId }) {
        const formData = new FormData();
        formData.append('action', 'fetch_realtors_ajax');
        formData.append('nonce', rtRealtorAjax.edit_nonce);
        formData.append('page', page);
        formData.append('rows', rows);
        formData.append('search', search);

        const result = await ajaxFetch(formData);
        const tbody = document.getElementById(bodyId);
        const pagination = document.getElementById(paginationId);
        if (!tbody || !pagination) return;

        tbody.innerHTML = '';
        pagination.innerHTML = '';

        if (result.success && result.data.realtors.length > 0) {
            result.data.realtors.forEach(realtor => {
                const tr = document.createElement('tr');
                tr.dataset.realtorId = realtor.realtor_id;
                tr.innerHTML = `
                    <td><img src="${realtor.profile_picture || rtRealtorAjax.default_avatar}" style="width:40px;height:40px;border-radius:50%;object-fit:cover;"></td>
                    <td class="realtor-name-text">${realtor.full_name}</td>
                    <td>${realtor.email || ''}</td>
                    <td>${realtor.phone || ''}</td>
                    <td>${realtor.agency_name || ''}</td>
                    <td>${realtor.license_number || ''}</td>
                    <td>${realtor.rating_avg || ''}</td>
                    <td>
                        <span class="editRealtorBtn" style="cursor:pointer;">✏️</span>
                        <span class="deleteRealtorBtn" style="cursor:pointer;">🗑️</span>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            for (let i = 1; i <= result.data.total_pages; i++) {
                const btn = document.createElement('button');
                btn.textContent = i;
                if (i === page) btn.classList.add('active');
                btn.addEventListener('click', () => fetchRealtors({ page: i, rows, search, bodyId, paginationId }));
                pagination.appendChild(btn);
            }

            setupRowButtons(bodyId);
        } else {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;">No realtors found</td></tr>`;
        }
    };

    // ---------------------------
    // Create Realtor
    // ---------------------------
    const createForm = document.getElementById('createRealtorForm');
    if (createForm) {
        const newForm = createForm.cloneNode(true);
        createForm.parentNode.replaceChild(newForm, createForm);
        document.getElementById('createRealtorForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn.disabled) return;

            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating...';

            const formData = new FormData(this);
            formData.append('action', 'create_realtor_ajax');
            formData.append('nonce', rtRealtorAjax.create_nonce);

            try {
                const result = await ajaxFetch(formData);
                if (result.success) {
                    showNotification('Realtor created successfully!');
                    this.reset();
                    document.getElementById('createPreviewAvatar').src = rtRealtorAjax.default_avatar;
                    document.getElementById('amRealtorCreateModal').style.display = 'none';
                    setTimeout(() => {
                        fetchRealtors({
                            page: 1,
                            rows: parseInt(document.getElementById('realtorRows').value, 10),
                            search: document.getElementById('realtorSearch').value.trim(),
                            bodyId: 'realtorBody',
                            paginationId: 'realtorPagination'
                        });
                    }, 500);
                } else {
                    showNotification('Error: ' + result.data, 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Create Realtor';
                }
            } catch (error) {
                showNotification('Network error. Please try again.', 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Create Realtor';
            }
        });
    }

    // ---------------------------
    // Edit Realtor
    // ---------------------------
    const editForm = document.getElementById('editRealtorForm');
    if (editForm) {
        editForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            if (isProcessing) return;
            isProcessing = true;

            const submitBtn = editForm.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Updating...';

            const formData = new FormData(editForm);
            formData.append('action', 'update_realtor_ajax');
            formData.append('nonce', rtRealtorAjax.edit_nonce);

            try {
                const result = await ajaxFetch(formData);
                if (result.success) {
                    showNotification('Realtor updated successfully!');
                    document.getElementById('amRealtorEditModal').style.display = 'none';
                    await fetchRealtors({
                        page: 1,
                        rows: parseInt(document.getElementById('realtorRows').value, 10),
                        search: document.getElementById('realtorSearch').value.trim(),
                        bodyId: 'realtorBody',
                        paginationId: 'realtorPagination'
                    });
                } else {
                    showNotification('Error: ' + result.data, 'error');
                }
            } catch (error) {
                showNotification('Network error. Please try again.', 'error');
            } finally {
                isProcessing = false;
                submitBtn.disabled = false;
                submitBtn.textContent = 'Update Realtor';
            }
        });
    }

    // ---------------------------
    // Modal & profile preview
    // ---------------------------
    function initializeModalFunctionality() {
        const createModal = document.getElementById('amRealtorCreateModal');
        const addBtn = document.querySelector('.rt-btn-create');
        const closeBtn = document.getElementById('closeRealtorCreateModal');
        const profileInput = document.getElementById('create_realtor_profile_picture');
        const previewAvatar = document.getElementById('createPreviewAvatar');

        if (addBtn && createModal) addBtn.addEventListener('click', () => createModal.style.display = 'flex');
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

        const editProfileInput = document.getElementById('edit_realtor_profile_picture');
        const editPreviewAvatar = document.getElementById('editPreviewAvatar');
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
    // Edit/Delete row buttons
    // ---------------------------
    function setupRowButtons(bodyId) {
        const tbody = document.getElementById(bodyId);
        if (!tbody) return;

        tbody.querySelectorAll('.editRealtorBtn').forEach(btn => {
            btn.addEventListener('click', async function () {
                const row = this.closest('tr');
                const realtorId = row.dataset.realtorId;
                if (!realtorId) return;

                const modal = document.getElementById('amRealtorEditModal');
                modal.style.display = 'flex';

                const formData = new FormData();
                formData.append('action', 'fetch_single_realtor_ajax');
                formData.append('nonce', rtRealtorAjax.edit_nonce);
                formData.append('realtor_id', realtorId);

                const result = await ajaxFetch(formData);
                if (result.success) {
                    const r = result.data;
                    document.getElementById('edit_realtor_id').value = r.realtor_id;
                    document.getElementById('edit_full_name').value = r.full_name;
                    document.getElementById('edit_email').value = r.email;
                    document.getElementById('edit_phone').value = r.phone;
                    document.getElementById('edit_agency_name').value = r.agency_name;
                    document.getElementById('edit_license_number').value = r.license_number;
                    document.getElementById('edit_rating_avg').value = r.rating_avg;
                    document.getElementById('editPreviewAvatar').src = r.profile_picture || rtRealtorAjax.default_avatar;
                } else {
                    showNotification('Error: ' + result.data, 'error');
                    modal.style.display = 'none';
                }
            });
        });

        tbody.querySelectorAll('.deleteRealtorBtn').forEach(btn => {
            btn.addEventListener('click', async function () {
                const row = this.closest('tr');
                const realtorId = row.dataset.realtorId;
                if (!realtorId) return;
                if (!confirm('Are you sure you want to delete this realtor?')) return;

                const formData = new FormData();
                formData.append('action', 'delete_realtor_ajax');
                formData.append('nonce', rtRealtorAjax.delete_nonce);
                formData.append('realtor_id', realtorId);

                const result = await ajaxFetch(formData);
                if (result.success) {
                    showNotification('Realtor deleted successfully!');
                    await fetchRealtors({
                        page: 1,
                        rows: parseInt(document.getElementById('realtorRows').value, 10),
                        search: document.getElementById('realtorSearch').value.trim(),
                        bodyId: 'realtorBody',
                        paginationId: 'realtorPagination'
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
            fetchRealtors({
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
        fetchRealtors({
            page: 1,
            rows: parseInt(document.getElementById('realtorRows').value, 10),
            search: document.getElementById('realtorSearch').value.trim(),
            bodyId: 'realtorBody',
            paginationId: 'realtorPagination'
        });
        setupSearchAndRows('realtorSearch', 'realtorRows', 'realtorBody', 'realtorPagination');
    }

    initialize();
});
