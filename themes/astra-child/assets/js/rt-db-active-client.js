document.addEventListener('DOMContentLoaded', function () {
    console.log('Dashboard Active Clients Script Loaded');
    
    let isProcessing = false;

    // AJAX wrapper with better error handling
    async function ajaxFetch(formData) {
        try {
            const response = await fetch(rtDashboardAjax.ajax_url, { 
                method: 'POST', 
                body: formData 
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            console.log('AJAX Response:', result);
            return result;
            
        } catch (error) {
            console.error('AJAX Fetch Error:', error);
            showNotification('Network error: ' + error.message, 'error');
            return { success: false, data: error.message };
        }
    }

    // Notification system
    function showNotification(msg, type = 'success') {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.dashboard-notification');
        existingNotifications.forEach(notification => notification.remove());

        const notification = document.createElement('div');
        notification.className = 'dashboard-notification ' + type;
        notification.style.cssText = `
            position: fixed; top: 20px; right: 20px; padding: 15px 20px;
            background: ${type === 'success' ? '#4CAF50' : '#f44336'};
            color: white; border-radius: 4px; z-index: 10000; font-weight: 500;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        `;
        notification.textContent = msg;
        document.body.appendChild(notification);

        // Auto remove after 3 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 3000);
    }

    // Render pagination buttons
    function renderPagination(container, totalPages, currentPage) {
        const paginationElement = document.getElementById(container);
        if (!paginationElement) return;
        
        if (totalPages <= 1) {
            paginationElement.innerHTML = '';
            return;
        }

        let html = '';
        
        // Previous button
        if (currentPage > 1) {
            html += `<button class="page-btn prev-btn" data-page="${currentPage - 1}">←</button>`;
        }
        
        // Page numbers
        for(let i = 1; i <= totalPages; i++) {
            if (totalPages <= 7 || i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                html += `<button class="page-btn ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`;
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                html += `<span class="page-dots">...</span>`;
            }
        }
        
        // Next button
        if (currentPage < totalPages) {
            html += `<button class="page-btn next-btn" data-page="${currentPage + 1}">→</button>`;
        }
        
        paginationElement.innerHTML = html;
    }

    // Load Active Clients with Pagination
    async function loadActiveClients() {
        console.log('Loading active clients...');
        
        const activePagination = document.getElementById('activeClientsPagination');
        const pageBtn = activePagination ? activePagination.querySelector('.page-btn.active') : null;
        const page = pageBtn ? parseInt(pageBtn.dataset.page) : 1;
        const rows = parseInt(document.getElementById('activeClientsRows').value) || 10;
        const search = document.getElementById('activeClientsSearch').value || '';

        const bodyContainer = 'activeClientsBody';
        const paginationContainer = 'activeClientsPagination';

        document.getElementById(bodyContainer).innerHTML = '<tr><td colspan="5" style="text-align:center;padding:20px;">Loading...</td></tr>';

        try {
            const formData = new FormData();
            formData.append('action', 'get_active_clients_page');
            formData.append('nonce', rtDashboardAjax.pagination_nonce);
            formData.append('page', page);
            formData.append('rows_per_page', rows);
            formData.append('search', search);

            console.log('Sending AJAX request with:', {
                action: 'get_active_clients_page',
                page: page,
                rows: rows,
                search: search
            });

            const result = await ajaxFetch(formData);
            
            if (result.success) {
                console.log('Data loaded successfully:', result.data);
                document.getElementById(bodyContainer).innerHTML = result.data.html;
                renderPagination(paginationContainer, result.data.total_pages, result.data.current_page);
            } else {
                console.error('Server returned error:', result.data);
                document.getElementById(bodyContainer).innerHTML = '<tr><td colspan="5" style="text-align:center;color:red;">Error: ' + (result.data || 'Unknown error') + '</td></tr>';
            }
        } catch (error) {
            console.error('Load Active Clients Error:', error);
            document.getElementById(bodyContainer).innerHTML = '<tr><td colspan="5" style="text-align:center;color:red;">AJAX Request Failed: ' + error.message + '</td></tr>';
        }
    }

    // --------------------------- Create Client ---------------------------
    const createClientForm = document.getElementById('rtDbClientCreateForm');
    if (createClientForm) {
        createClientForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            if (isProcessing) return;
            isProcessing = true;

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating...';

            try {
                const formData = new FormData(this);
                formData.append('action', 'create_active_client_ajax');
                formData.append('nonce', rtDashboardAjax.create_nonce);

                console.log('Creating client with:', {
                    full_name: formData.get('full_name'),
                    email: formData.get('email')
                });

                const result = await ajaxFetch(formData);

                if (result.success) {
                    showNotification('Active client created successfully!');
                    this.reset();
                    const modal = document.getElementById('rtDbClientCreateModal');
                    if (modal) modal.style.display = 'none';
                    
                    // Refresh the table with first page
                    loadActiveClients();
                } else {
                    showNotification('Error: ' + (result.data || 'Unknown error'), 'error');
                }
            } catch (error) {
                showNotification('Network error: ' + error.message, 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                isProcessing = false;
            }
        });
    }

    // --------------------------- Delete Client ---------------------------
    document.addEventListener('click', async function (e) {
        if (e.target.classList.contains('delete-client-btn')) {
            const row = e.target.closest('tr');
            const clientId = row.dataset.clientId;
            if (!clientId || !confirm('Are you sure you want to delete this client?')) return;

            try {
                const formData = new FormData();
                formData.append('action', 'delete_active_client_ajax');
                formData.append('nonce', rtDashboardAjax.delete_nonce);
                formData.append('client_id', clientId);

                const result = await ajaxFetch(formData);

                if (result.success) {
                    showNotification('Client deleted successfully!');
                    // Remove the row and refresh pagination if needed
                    row.remove();
                    
                    // Reload the table to update pagination
                    setTimeout(() => {
                        loadActiveClients();
                    }, 500);
                } else {
                    showNotification('Error: ' + (result.data || 'Unknown error'), 'error');
                }
            } catch (error) {
                showNotification('Network error: ' + error.message, 'error');
            }
        }
    });

    // --------------------------- Modal Handling ---------------------------
    const addClientBtn = document.getElementById('addClientBtn');
    const clientCreateModal = document.getElementById('rtDbClientCreateModal');
    const closeClientCreateBtn = document.getElementById('closeRtDbClientCreateModal');

    if (addClientBtn && clientCreateModal) {
        addClientBtn.addEventListener('click', () => {
            console.log('Opening create modal');
            clientCreateModal.style.display = 'flex';
        });
    }
    
    if (closeClientCreateBtn && clientCreateModal) {
        closeClientCreateBtn.addEventListener('click', () => {
            clientCreateModal.style.display = 'none';
        });
        
        clientCreateModal.addEventListener('click', e => {
            if (e.target === clientCreateModal) {
                clientCreateModal.style.display = 'none';
            }
        });
    }

    // Profile picture preview for create modal
    const createProfileInput = document.getElementById('create_rt_db_active_profile_picture');
    const createPreviewAvatar = document.getElementById('createRtDbActivePreviewAvatar');
    
    if (createProfileInput && createPreviewAvatar) {
        createProfileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => createPreviewAvatar.src = e.target.result;
                reader.readAsDataURL(file);
            }
        });
    }

    // --------------------------- Event Listeners for Search & Pagination ---------------------------
    
    // Search functionality with debounce
    let searchTimeout;
    const searchInput = document.getElementById('activeClientsSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                console.log('Searching for:', this.value);
                loadActiveClients();
            }, 500);
        });
    }

    // Rows per page change
    const rowsSelect = document.getElementById('activeClientsRows');
    if (rowsSelect) {
        rowsSelect.addEventListener('change', function() {
            console.log('Rows changed to:', this.value);
            loadActiveClients();
        });
    }

    // Pagination button clicks
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('page-btn')) {
            const page = e.target.dataset.page;
            const paginationContainer = e.target.closest('.pagination');
            
            if (!page) return;
            
            console.log('Page changed to:', page);
            
            // Remove active class from all buttons
            paginationContainer.querySelectorAll('.page-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Add active class to clicked button
            e.target.classList.add('active');
            
            loadActiveClients();
        }
    });

    // --------------------------- Initial Load ---------------------------
    console.log('Initializing dashboard...');
    loadActiveClients();
});