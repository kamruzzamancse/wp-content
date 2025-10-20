<?php
if (!defined('ABSPATH')) exit; // Direct access block

global $wpdb;

/**
 * -----------------------------
 * Active Clients Pagination AJAX
 * -----------------------------
 */
add_action('wp_ajax_get_active_clients_page', 'get_active_clients_page');
add_action('wp_ajax_nopriv_get_active_clients_page', 'get_active_clients_page');

function get_active_clients_page() {
    check_ajax_referer('clients_pagination_nonce', 'nonce');
    global $wpdb;

    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $rows_per_page = isset($_POST['rows_per_page']) ? intval($_POST['rows_per_page']) : 10;
    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $offset = ($page - 1) * $rows_per_page;

    $where = "WHERE (deleted_at IS NULL OR deleted_at = '') AND (LOWER(status) = 'active' OR status IS NULL OR status = '')";
    if(!empty($search)) {
        $where .= $wpdb->prepare(" AND full_name LIKE %s", "%{$search}%");
    }

    $total = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}clients {$where}");
    $clients = $wpdb->get_results("
        SELECT client_id, full_name, email, phone, note
        FROM {$wpdb->prefix}clients
        {$where}
        ORDER BY created_at DESC
        LIMIT {$rows_per_page} OFFSET {$offset}
    ");

    ob_start();
    if($clients):
        foreach($clients as $client):
            ?>
            <tr data-client-id="<?php echo esc_attr($client->client_id); ?>">
                <td data-label="Client Name"><?php echo esc_html($client->full_name); ?></td>
                <td data-label="Email"><?php echo esc_html($client->email ?: '—'); ?></td>
                <td data-label="Phone"><?php echo esc_html($client->phone ?: '—'); ?></td>
                <td data-label="Notes"><?php echo esc_html($client->note ?: '—'); ?></td>
                <td data-label="Actions" class="action-cell">
                    <span class="delete-client-btn" title="Delete">🗑️</span>
                </td>
            </tr>
            <?php
        endforeach;
    else:
        echo '<tr><td colspan="5" style="text-align:center;">No Active Clients Found</td></tr>';
    endif;
    $html = ob_get_clean();

    wp_send_json_success([
        'html' => $html,
        'total_pages' => ceil($total / $rows_per_page),
        'current_page' => $page
    ]);
}

/**
 * -----------------------------
 * Leads Pagination AJAX
 * -----------------------------
 */
add_action('wp_ajax_get_leads_page', 'get_leads_page');
add_action('wp_ajax_nopriv_get_leads_page', 'get_leads_page');

function get_leads_page() {
    check_ajax_referer('leads_pagination_nonce', 'nonce');
    global $wpdb;

    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $rows_per_page = isset($_POST['rows_per_page']) ? intval($_POST['rows_per_page']) : 10;
    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $offset = ($page - 1) * $rows_per_page;

    $where = "WHERE (deleted_at IS NULL OR deleted_at = '') AND (LOWER(status) = 'lead' OR status IS NULL OR status = '')";
    if(!empty($search)) {
        $where .= $wpdb->prepare(" AND full_name LIKE %s", "%{$search}%");
    }

    $total = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}clients {$where}");
    $leads = $wpdb->get_results("
        SELECT client_id, full_name, note, lead_status
        FROM {$wpdb->prefix}clients
        {$where}
        ORDER BY created_at DESC
        LIMIT {$rows_per_page} OFFSET {$offset}
    ");

    ob_start();
    if($leads):
        foreach($leads as $lead):
            $lead_status = strtolower($lead->lead_status ?: 'cold');
            $status_label = ucfirst($lead_status);
            $status_color = '';
            switch($lead_status){
                case 'hot': $status_color='background-color:#ff4d4d;'; break;
                case 'warm': $status_color='background-color:#ffc107;'; break;
                case 'cold': default: $status_color='background-color:#4caf50;'; break;
            }
            ?>
            <tr data-client-id="<?php echo esc_attr($lead->client_id); ?>">
                <td data-label="Client Name"><?php echo esc_html($lead->full_name); ?></td>
                <td data-label="Last Touch">—</td>
                <td data-label="Status"><span class="status-dot" style="<?php echo esc_attr($status_color); ?>"></span> <?php echo esc_html($status_label); ?></td>
                <td data-label="Notes"><?php echo esc_html($lead->note ?: '—'); ?></td>
                <td data-label="Actions" class="action-cell">
                    <span class="edit-lead-btn" title="Edit">✏️</span>
                    <span class="convert-lead-btn" title="Convert to Client">🔄</span>
                    <span class="delete-lead-btn" title="Delete">🗑️</span>
                </td>
            </tr>
            <?php
        endforeach;
    else:
        echo '<tr><td colspan="5" style="text-align:center;">No Leads Found</td></tr>';
    endif;
    $html = ob_get_clean();

    wp_send_json_success([
        'html' => $html,
        'total_pages' => ceil($total / $rows_per_page),
        'current_page' => $page
    ]);
}

<!-- CREATE CLIENT / LEAD -->
<script>
// CREATE CLIENT / LEAD MODAL + AJAX
document.addEventListener('DOMContentLoaded', function() {

    const addClientBtn = document.getElementById('addClientBtn');
    const addLeadBtn = document.getElementById('addLeadBtn');
    const createModal = document.getElementById('rmRealtorClientCreateModal');
    const closeCreateBtn = document.getElementById('closeRealtorClientCreateModal');
    const createForm = document.getElementById('createRealtorClientForm');
    const createProfileInput = document.getElementById('create_realtor_client_profile_picture');

    // Open Add Client modal
    if (addClientBtn && createModal) {
        addClientBtn.addEventListener('click', () => {
            if (createForm) createForm.reset();
            createModal.style.display = 'flex';
        });
    }

    // Open Add Lead modal and set status to "lead"
    if (addLeadBtn && createModal) {
        addLeadBtn.addEventListener('click', () => {
            if (createForm) createForm.reset();
            const statusInput = document.getElementById('create_realtor_client_status');
            if (statusInput) statusInput.value = 'lead';
            const previewAvatar = document.getElementById('createRealtorClientPreviewAvatar');
            if (previewAvatar) previewAvatar.src = "<?php echo esc_url(wp_upload_dir()['baseurl'] . '/2025/08/client-photo.jpg'); ?>";
            createModal.style.display = 'flex';
        });
    }

    // Close modal
    if (closeCreateBtn && createModal) {
        closeCreateBtn.addEventListener('click', () => createModal.style.display = 'none');
        createModal.addEventListener('click', e => {
            if (e.target === createModal) createModal.style.display = 'none';
        });
    }

    // Profile picture preview
    if (createProfileInput) {
        createProfileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => {
                    const previewAvatar = document.getElementById('createRealtorClientPreviewAvatar');
                    if (previewAvatar) previewAvatar.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // AJAX submit
    if (createForm) {
        createForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const fullName = document.getElementById('create_realtor_client_full_name').value.trim();
            const email = document.getElementById('create_realtor_client_email').value.trim();
            const status = document.getElementById('create_realtor_client_status').value;

            if (!fullName || !email || !status) {
                alert('Please fill in all required fields (Name, Email, Status)');
                return;
            }

            const formData = new FormData(createForm);
            formData.append('action', 'create_realtor_client_ajax');
            formData.append('nonce', '<?php echo wp_create_nonce("cl_client_create_nonce"); ?>');

            const submitBtn = createForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Creating...';
            submitBtn.disabled = true;

            fetch('<?php echo admin_url("admin-ajax.php"); ?>', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Client created successfully!');
                        createForm.reset();
                        const previewAvatar = document.getElementById('createRealtorClientPreviewAvatar');
                        if (previewAvatar) previewAvatar.src = "<?php echo esc_url(wp_upload_dir()['baseurl'] . '/2025/08/client-photo.jpg'); ?>";
                        createModal.style.display = 'none';
                        setTimeout(() => window.location.reload(), 800);
                    } else {
                        alert('Error: ' + data.data);
                    }
                })
                .catch(() => alert('Network error. Please try again.'))
                .finally(() => { submitBtn.textContent = originalText; submitBtn.disabled = false; });
        });
    }

});
</script>

<script>
// EDIT CLIENT / LEAD MODAL + AJAX
document.addEventListener('DOMContentLoaded', function() {

    const editModal = document.getElementById('rmRealtorClientEditModal');
    const closeEditBtn = document.getElementById('closeRealtorClientEditModal');
    const editForm = document.getElementById('editRealtorClientForm');
    const editProfileInput = document.getElementById('edit_realtor_client_profile_picture');

    document.querySelectorAll('.edit-client-btn, .edit-lead-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const clientId = this.closest('tr').dataset.clientId;
            if (!clientId || !editModal) return;

            editModal.style.display = 'flex';

            fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                method: 'POST',
                body: new URLSearchParams({
                    action: 'fetch_realtor_client_ajax',
                    nonce: '<?php echo wp_create_nonce("cl_client_edit_nonce"); ?>',
                    client_id: clientId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const client = data.data;
                    document.getElementById('edit_realtor_client_id').value = client.client_id;
                    document.getElementById('edit_realtor_client_full_name').value = client.full_name;
                    document.getElementById('edit_realtor_client_email').value = client.email;
                    document.getElementById('edit_realtor_client_phone').value = client.phone;
                    document.getElementById('edit_realtor_client_notes').value = client.note;
                    document.getElementById('edit_realtor_client_status').value = client.status;
                    document.getElementById('editRealtorClientPreviewAvatar').src = client.profile_picture || "<?php echo esc_url(wp_upload_dir()['baseurl'] . '/2025/08/client-photo.jpg'); ?>";

                    // === Show Lead Status dropdown only for leads ===
                    const leadStatusRow = document.getElementById('leadStatusRow');
                    const leadStatusSelect = document.getElementById('edit_realtor_lead_status');

                    if (client.status === 'lead') {
                        leadStatusRow.style.display = 'flex';
                        // Set current lead_status value if exists, else default to 'cold'
                        if (client.lead_status) {
                            leadStatusSelect.value = client.lead_status;
                        } else {
                            leadStatusSelect.value = 'cold';
                        }
                    } else {
                        leadStatusRow.style.display = 'none';
                    }
                } else {
                    alert('Failed to fetch client data');
                    editModal.style.display = 'none';
                }
            })
            .catch(() => { alert('Network error. Please try again.'); editModal.style.display = 'none'; });
        });
    });

    // Close modal
    if (closeEditBtn) {
        closeEditBtn.addEventListener('click', () => editModal.style.display = 'none');
        editModal.addEventListener('click', e => { if (e.target === editModal) editModal.style.display = 'none'; });
    }

    // Profile picture preview
    if (editProfileInput) {
        editProfileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => { document.getElementById('editRealtorClientPreviewAvatar').src = e.target.result; };
                reader.readAsDataURL(file);
            }
        });
    }

    // AJAX submit
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(editForm);
            formData.append('action', 'update_realtor_client_ajax');
            formData.append('nonce', '<?php echo wp_create_nonce("cl_client_edit_nonce"); ?>');

            const submitBtn = editForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Updating...';
            submitBtn.disabled = true;

            fetch('<?php echo admin_url("admin-ajax.php"); ?>', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Client updated successfully!');
                        editForm.reset();
                        editModal.style.display = 'none';
                        setTimeout(() => window.location.reload(), 800);
                    } else {
                        alert('Error: ' + data.data);
                    }
                })
                .catch(() => alert('Network error. Please try again.'))
                .finally(() => { submitBtn.textContent = originalText; submitBtn.disabled = false; });
        });
    }

});
</script>

<!-- DELETE CLIENT / LEAD -->
<script>
// DELETE CLIENT / LEAD AJAX
document.addEventListener('DOMContentLoaded', function() {

    document.querySelectorAll('.delete-client-btn, .delete-lead-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const clientId = row.dataset.clientId;
            if (!clientId) return;

            if (!confirm('Are you sure you want to delete this client/lead?')) return;

            const formData = new FormData();
            formData.append('action', 'delete_realtor_client_ajax');
            formData.append('nonce', '<?php echo wp_create_nonce("cl_client_delete_nonce"); ?>');
            formData.append('client_id', clientId);

            const btnText = this.textContent;
            this.textContent = 'Deleting...';
            this.disabled = true;

            fetch('<?php echo admin_url("admin-ajax.php"); ?>', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Client deleted successfully!');
                        row.remove();
                    } else {
                        alert('Error: ' + data.data);
                        this.textContent = btnText;
                        this.disabled = false;
                    }
                })
                .catch(err => {
                    alert('Network error: ' + err.message);
                    this.textContent = btnText;
                    this.disabled = false;
                });
        });
    });

});
</script>

<!-- CONVERT LEAD TO CLIENT -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    const convertBtns = document.querySelectorAll('.convert-lead-btn');

    convertBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const clientId = row.dataset.clientId;
            if (!clientId) return;

            if (!confirm('Do you want to convert this lead to a client?')) return;

            const formData = new FormData();
            formData.append('action', 'convert_lead_to_client');
            formData.append('nonce', '<?php echo wp_create_nonce("convert_lead_nonce"); ?>');
            formData.append('client_id', clientId);

            btn.textContent = 'Converting...';
            btn.disabled = true;

            fetch('<?php echo admin_url("admin-ajax.php"); ?>', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Lead successfully converted to client!');

                        // Remove the row from Leads table
                        row.remove();

                        // Add the row to Active Clients table
                        const activeClientsTable = document.querySelector('.active-clients-table tbody');
                        if (activeClientsTable) {
                            const newRow = document.createElement('tr');
                            newRow.dataset.clientId = clientId;
                            newRow.innerHTML = `
                                <td data-label="Client Name">${row.querySelector('[data-label="Client Name"]').textContent}</td>
                                <td data-label="Email">${row.querySelector('[data-label="Email"]')?.textContent || '—'}</td>
                                <td data-label="Phone">${row.querySelector('[data-label="Phone"]')?.textContent || '—'}</td>
                                <td data-label="Notes">${row.querySelector('[data-label="Notes"]')?.textContent || '—'}</td>
                                <td data-label="Actions" class="action-cell">
                                    <span class="delete-client-btn" title="Delete">🗑️</span>
                                </td>
                            `;
                            activeClientsTable.prepend(newRow);

                            // Rebind delete event for new row
                            newRow.querySelector('.delete-client-btn').addEventListener('click', function() {
                                const clientRow = this.closest('tr');
                                const clientId = clientRow.dataset.clientId;
                                if (!clientId) return;
                                if (!confirm('Are you sure you want to delete this client?')) return;

                                const fd = new FormData();
                                fd.append('action', 'delete_realtor_client_ajax');
                                fd.append('nonce', '<?php echo wp_create_nonce("cl_client_delete_nonce"); ?>');
                                fd.append('client_id', clientId);

                                const btnText = this.textContent;
                                this.textContent = 'Deleting...';
                                this.disabled = true;

                                fetch('<?php echo admin_url("admin-ajax.php"); ?>', { method: 'POST', body: fd })
                                    .then(res => res.json())
                                    .then(data => {
                                        if (data.success) clientRow.remove();
                                        else { alert('Error: ' + data.data); this.textContent = btnText; this.disabled = false; }
                                    })
                                    .catch(err => { alert('Network error: ' + err.message); this.textContent = btnText; this.disabled = false; });
                            });
                        }
                    } else {
                        alert('Error: ' + data.data);
                        btn.textContent = '🔄';
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    alert('Network error: ' + err.message);
                    btn.textContent = '🔄';
                    btn.disabled = false;
                });
        });
    });

});
</script>
