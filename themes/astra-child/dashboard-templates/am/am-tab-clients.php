<div class="ab-container">
    <div class="ab-table-header">
        <div class="ab-header-left">
            <h1 class="header-title">Clients</h1>
        </div>
        <div class="ab-header-right">
            <div class="ab-search-box">
                <span class="pt-search-icon">🔍</span>
                <input type="text" class="pt-search-input" placeholder="Search: Realtor Name">
            </div>
            <div class="ab-action-buttons">
                <button class="ab-btn ab-btn-import">
                    <span class="dashicons dashicons-upload"></span> Import
                </button>
                <button class="ab-btn ab-btn-export">
                    <span class="dashicons dashicons-download"></span> Export
                </button>
                <button class="ab-btn ab-btn-create ab-openCreateAdminClient">
                    <span class="dashicons dashicons-plus-alt"></span> Add Client
                </button>
            </div>
        </div>
    </div>

    <table class="clients-table">
        <thead>
            <tr>
                <th class="ab-sl-column">#</th>
                <th>Client Name</th>
                <th>Email</th>
                <th>Phone Number</th>
                <th>Address</th>
                <th class="ab-actions-column">Actions</th>
            </tr>
        </thead>
        <tbody>
    <!-- Row 01 -->
    <tr>
        <td class="ab-sl-column" data-label="#SL">01</td>
        <td data-label="Client Name">
            <span class="ab-viewClientDetails clickable-name"
                data-id="1"
                data-name="Afsana Hamid Mim"
                data-email="afsana.mim@example.com"
                data-phone="999-888-666"
                data-address="New York, NY"
                data-company="Best Realty"
                data-broker="BRK-2025-1234"
                data-avatar="<?php echo esc_url(wp_upload_dir()['baseurl'].'/2025/08/client1.jpg'); ?>">
                Afsana Hamid Mim
            </span>
        </td>
        <td data-label="Email">afsana.mim@example.com</td>
        <td data-label="Phone Number">999-888-666</td>
        <td data-label="Address">New York, NY</td>
        <td class="ab-actions-column" data-label="Actions">
            <div class="ab-action-icons">
                <span class="ab-action-icon ab-viewClientDetails" title="View"
                      data-id="1" data-name="Afsana Hamid Mim"
                      data-email="afsana.mim@example.com"
                      data-phone="999-888-666"
                      data-address="New York, NY"
                      data-company="Best Realty"
                      data-broker="BRK-2025-1234"
                      data-avatar="<?php echo esc_url(wp_upload_dir()['baseurl'].'/2025/08/client1.jpg'); ?>">👁️</span>
                <span class="ab-action-icon ab-editClient" title="Edit">✏️</span>
                <span class="ab-action-icon" title="Delete">🗑️</span>
            </div>
        </td>
    </tr>

    <!-- Row 02 -->
    <tr>
        <td class="ab-sl-column" data-label="#SL">02</td>
        <td data-label="Client Name">
            <span class="ab-viewClientDetails clickable-name"
                data-id="2"
                data-name="Liam Anderson"
                data-email="liam.anderson@realtorspro.com"
                data-phone="888-123-4567"
                data-address="Dallas, TX"
                data-company="Realtors Pro"
                data-broker="BRK-2025-5678"
                data-avatar="<?php echo esc_url(wp_upload_dir()['baseurl'].'/2025/08/client2.jpg'); ?>">
                Liam Anderson
            </span>
        </td>
        <td data-label="Email">liam.anderson@realtorspro.com</td>
        <td data-label="Phone Number">888-123-4567</td>
        <td data-label="Address">Dallas, TX</td>
        <td class="ab-actions-column" data-label="Actions">
            <div class="ab-action-icons">
                <span class="ab-action-icon ab-viewClientDetails" title="View"
                      data-id="2" data-name="Liam Anderson"
                      data-email="liam.anderson@realtorspro.com"
                      data-phone="888-123-4567"
                      data-address="Dallas, TX"
                      data-company="Realtors Pro"
                      data-broker="BRK-2025-5678"
                      data-avatar="<?php echo esc_url(wp_upload_dir()['baseurl'].'/2025/08/client2.jpg'); ?>">👁️</span>
                <span class="ab-action-icon ab-editClient" title="Edit">✏️</span>
                <span class="ab-action-icon" title="Delete">🗑️</span>
            </div>
        </td>
    </tr>

    <!-- Row 03 -->
    <tr>
        <td class="ab-sl-column" data-label="#SL">03</td>
        <td data-label="Client Name">
            <span class="ab-viewClientDetails clickable-name"
                data-id="3"
                data-name="Amelia Johnson"
                data-email="amelia.johnson@homesales.net"
                data-phone="555-777-2323"
                data-address="Orlando, FL"
                data-company="Home Sales"
                data-broker="BRK-2025-9012"
                data-avatar="<?php echo esc_url(wp_upload_dir()['baseurl'].'/2025/08/client3.jpg'); ?>">
                Amelia Johnson
            </span>
        </td>
        <td data-label="Email">amelia.johnson@homesales.net</td>
        <td data-label="Phone Number">555-777-2323</td>
        <td data-label="Address">Orlando, FL</td>
        <td class="ab-actions-column" data-label="Actions">
            <div class="ab-action-icons">
                <span class="ab-action-icon ab-viewClientDetails" title="View"
                      data-id="3" data-name="Amelia Johnson"
                      data-email="amelia.johnson@homesales.net"
                      data-phone="555-777-2323"
                      data-address="Orlando, FL"
                      data-company="Home Sales"
                      data-broker="BRK-2025-9012"
                      data-avatar="<?php echo esc_url(wp_upload_dir()['baseurl'].'/2025/08/client3.jpg'); ?>">👁️</span>
                <span class="ab-action-icon ab-editClient" title="Edit">✏️</span>
                <span class="ab-action-icon" title="Delete">🗑️</span>
            </div>
        </td>
    </tr>

    <!-- Row 04 -->
    <tr>
        <td class="ab-sl-column" data-label="#SL">04</td>
        <td data-label="Client Name">
            <span class="ab-viewClientDetails clickable-name"
                data-id="4"
                data-name="Noah Wilson"
                data-email="noah.wilson@estateplus.org"
                data-phone="321-654-9870"
                data-address="Atlanta, GA"
                data-company="Estate Plus"
                data-broker="BRK-2025-3456"
                data-avatar="<?php echo esc_url(wp_upload_dir()['baseurl'].'/2025/08/client4.jpg'); ?>">
                Noah Wilson
            </span>
        </td>
        <td data-label="Email">noah.wilson@estateplus.org</td>
        <td data-label="Phone Number">321-654-9870</td>
        <td data-label="Address">Atlanta, GA</td>
        <td class="ab-actions-column" data-label="Actions">
            <div class="ab-action-icons">
                <span class="ab-action-icon ab-viewClientDetails" title="View"
                      data-id="4" data-name="Noah Wilson"
                      data-email="noah.wilson@estateplus.org"
                      data-phone="321-654-9870"
                      data-address="Atlanta, GA"
                      data-company="Estate Plus"
                      data-broker="BRK-2025-3456"
                      data-avatar="<?php echo esc_url(wp_upload_dir()['baseurl'].'/2025/08/client4.jpg'); ?>">👁️</span>
                <span class="ab-action-icon ab-editClient" title="Edit">✏️</span>
                <span class="ab-action-icon" title="Delete">🗑️</span>
            </div>
        </td>
    </tr>

    <!-- Row 05 -->
    <tr>
        <td class="ab-sl-column" data-label="#SL">05</td>
        <td data-label="Client Name">
            <span class="ab-viewClientDetails clickable-name"
                data-id="5"
                data-name="Emma Davis"
                data-email="emma.davis@realtymarket.io"
                data-phone="707-555-9087"
                data-address="San Diego, CA"
                data-company="Realty Market"
                data-broker="BRK-2025-7890"
                data-avatar="<?php echo esc_url(wp_upload_dir()['baseurl'].'/2025/08/client5.jpg'); ?>">
                Emma Davis
            </span>
        </td>
        <td data-label="Email">emma.davis@realtymarket.io</td>
        <td data-label="Phone Number">707-555-9087</td>
        <td data-label="Address">San Diego, CA</td>
        <td class="ab-actions-column" data-label="Actions">
            <div class="ab-action-icons">
                <span class="ab-action-icon ab-viewClientDetails" title="View"
                      data-id="5" data-name="Emma Davis"
                      data-email="emma.davis@realtymarket.io"
                      data-phone="707-555-9087"
                      data-address="San Diego, CA"
                      data-company="Realty Market"
                      data-broker="BRK-2025-7890"
                      data-avatar="<?php echo esc_url(wp_upload_dir()['baseurl'].'/2025/08/client5.jpg'); ?>">👁️</span>
                <span class="ab-action-icon ab-editClient" title="Edit">✏️</span>
                <span class="ab-action-icon" title="Delete">🗑️</span>
            </div>
        </td>
    </tr>

    <!-- Row 06 -->
    <tr>
        <td class="ab-sl-column" data-label="#SL">06</td>
        <td data-label="Client Name">
            <span class="ab-viewClientDetails clickable-name"
                data-id="6"
                data-name="William Moore"
                data-email="william.moore@prohomes.biz"
                data-phone="800-333-4466"
                data-address="Phoenix, AZ"
                data-company="Pro Homes"
                data-broker="BRK-2025-4321"
                data-avatar="<?php echo esc_url(wp_upload_dir()['baseurl'].'/2025/08/client6.jpg'); ?>">
                William Moore
            </span>
        </td>
        <td data-label="Email">william.moore@prohomes.biz</td>
        <td data-label="Phone Number">800-333-4466</td>
        <td data-label="Address">Phoenix, AZ</td>
        <td class="ab-actions-column" data-label="Actions">
            <div class="ab-action-icons">
                <span class="ab-action-icon ab-viewClientDetails" title="View"
                      data-id="6" data-name="William Moore"
                      data-email="william.moore@prohomes.biz"
                      data-phone="800-333-4466"
                      data-address="Phoenix, AZ"
                      data-company="Pro Homes"
                      data-broker="BRK-2025-4321"
                      data-avatar="<?php echo esc_url(wp_upload_dir()['baseurl'].'/2025/08/client6.jpg'); ?>">👁️</span>
                <span class="ab-action-icon ab-editClient" title="Edit">✏️</span>
                <span class="ab-action-icon" title="Delete">🗑️</span>
            </div>
        </td>
    </tr>

    <!-- Row 07 -->
    <tr>
        <td class="ab-sl-column" data-label="#SL">07</td>
        <td data-label="Client Name">
            <span class="ab-viewClientDetails clickable-name"
                data-id="7"
                data-name="Charlotte Lee"
                data-email="charlotte.lee@urbanestate.com"
                data-phone="609-901-7890"
                data-address="Seattle, WA"
                data-company="Urban Estate"
                data-broker="BRK-2025-6543"
                data-avatar="<?php echo esc_url(wp_upload_dir()['baseurl'].'/2025/08/client7.jpg'); ?>">
                Charlotte Lee
            </span>
        </td>
        <td data-label="Email">charlotte.lee@urbanestate.com</td>
        <td data-label="Phone Number">609-901-7890</td>
        <td data-label="Address">Seattle, WA</td>
        <td class="ab-actions-column" data-label="Actions">
            <div class="ab-action-icons">
                <span class="ab-action-icon ab-viewClientDetails" title="View"
                      data-id="7" data-name="Charlotte Lee"
                      data-email="charlotte.lee@urbanestate.com"
                      data-phone="609-901-7890"
                      data-address="Seattle, WA"
                      data-company="Urban Estate"
                      data-broker="BRK-2025-6543"
                      data-avatar="<?php echo esc_url(wp_upload_dir()['baseurl'].'/2025/08/client7.jpg'); ?>">👁️</span>
                <span class="ab-action-icon ab-editClient" title="Edit">✏️</span>
                <span class="ab-action-icon" title="Delete">🗑️</span>
            </div>
        </td>
    </tr>

    <!-- Row 08 -->
    <tr>
        <td class="ab-sl-column" data-label="#SL">08</td>
        <td data-label="Client Name">
            <span class="ab-viewClientDetails clickable-name"
                data-id="8"
                data-name="Benjamin Harris"
                data-email="ben.harris@luxuryrealtors.co"
                data-phone="444-222-9999"
                data-address="Las Vegas, NV"
                data-company="Luxury Realtors"
                data-broker="BRK-2025-8765"
                data-avatar="<?php echo esc_url(wp_upload_dir()['baseurl'].'/2025/08/client8.jpg'); ?>">
                Benjamin Harris
            </span>
        </td>
        <td data-label="Email">ben.harris@luxuryrealtors.co</td>
        <td data-label="Phone Number">444-222-9999</td>
        <td data-label="Address">Las Vegas, NV</td>
        <td class="ab-actions-column" data-label="Actions">
            <div class="ab-action-icons">
                <span class="ab-action-icon ab-viewClientDetails" title="View"
                      data-id="8" data-name="Benjamin Harris"
                      data-email="ben.harris@luxuryrealtors.co"
                      data-phone="444-222-9999"
                      data-address="Las Vegas, NV"
                      data-company="Luxury Realtors"
                      data-broker="BRK-2025-8765"
                      data-avatar="<?php echo esc_url(wp_upload_dir()['baseurl'].'/2025/08/client8.jpg'); ?>">👁️</span>
                <span class="ab-action-icon ab-editClient" title="Edit">✏️</span>
                <span class="ab-action-icon" title="Delete">🗑️</span>
            </div>
        </td>
    </tr>
</tbody>

    </table>
</div>

<?php
    include locate_template('dashboard-templates/am/am-client-details-modal.php');
    include locate_template('dashboard-templates/am/am-client-view-modal.php');
    include locate_template('dashboard-templates/am/am-client-create-modal.php');
    include locate_template('dashboard-templates/am/am-client-edit-modal.php');
?>

<style>
/* ---------------------- Responsive Table ---------------------- */
@media screen and (max-width: 768px) {
    table:not(.client-details),
    table:not(.client-details) thead,
    table:not(.client-details) tbody,
    table:not(.client-details) th,
    table:not(.client-details) tr {
        display: block;
        width: 100%;
    }
    table:not(.client-details) thead {
        display: none;
    }
    table:not(.client-details) tr {
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 12px;
        background: #f9f9ff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }
    table:not(.client-details) td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border: none;
        border-bottom: 1px solid #eee;
    }
    table:not(.client-details) td:last-child {
        border-bottom: none;
    }
    table:not(.client-details) td::before {
        content: attr(data-label);
        font-weight: 600;
        color: #333;
        flex: 1;
        text-align: left;
    }
    table:not(.client-details) .ab-actions-column {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }
    table:not(.client-details) .ab-action-icons {
        gap: 10px;
    }
    tbody td {
        max-width: none!important;
    }
}

@media screen and (max-width: 480px) {
    img {
        max-width: 200%;
    }
    .ab-actions-column {
        width: 100%;
    }
}

/* ---------------------- Table Styles ---------------------- */
table {
    width: 100%;
    border-collapse: collapse;
    font-family: Arial, sans-serif;
    font-size: 14px;
    background: #fff;
}
thead th {
    text-align: left;
    padding: 10px;
    border-bottom: 2px solid #ddd;
    font-weight: 600;
}
tbody td {
    padding: 10px;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
    max-width: 200px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Tooltip for truncated text */
tbody td:hover::after {
    content: attr(title);
    position: absolute;
    left: 0;
    top: 100%;
    background: #333;
    color: #fff;
    padding: 6px 10px;
    border-radius: 4px;
    white-space: normal;
    min-width: 200px;
    max-width: 400px;
    z-index: 1000;
    font-size: 13px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

/* Action Icons */
.ab-action-icons {
    display: flex;
    gap: 8px;
}
.ab-action-icon {
    cursor: pointer;
    font-size: 16px;
    transition: transform 0.2s;
}
.ab-action-icon:hover {
    transform: scale(1.2);
}

/* ---------------------- Modal Styles ---------------------- */
.modal-overlay {
    display: flex;
    visibility: hidden;
    opacity: 0;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.4);
    justify-content: center;
    align-items: center;
    transition: opacity 0.3s ease;
}
.modal-overlay.active {
    visibility: visible;
    opacity: 1;
}
.modal-content {
    background-color: #fff;
    padding: 30px;
    border-radius: 6px;
    max-width: 700px;
    width: 95%;
    position: relative;
    max-height: 90vh;
    overflow-y: auto;
}
.close-button {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 24px;
    cursor: pointer;
    color: #333;
}

.clickable-name {
    cursor: pointer;
    color: #0073aa;
    text-decoration: underline;
}
.clickable-name:hover {
    color: #005177;
}
.clients-table {
  border-collapse: separate !important;
  border-spacing: 0 !important;
  border-radius: 10px !important;
  overflow: hidden;
}

.clients-table thead th:first-child {
  border-top-left-radius: 10px !important;
}
.clients-table thead th:last-child {
  border-top-right-radius: 10px !important;
}
</style>

<!-- ======================  SCRIPTS ======================= -->
<script>
/* ---- View Realtor Modal (eye icon only) ---- */
document.addEventListener('DOMContentLoaded', function () {
    // Now targets only the eye action icon, not the clickable name
    const viewButtons = document.querySelectorAll('.ab-action-icon.ab-viewClientDetails');
    const modal = document.getElementById('amRealtorViewModal');
    const closeModalBtn = document.getElementById('closeRealtorModal');

    viewButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (modal) modal.classList.add('active');
        });
    });

    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', () => {
            if (modal) modal.classList.remove('active');
        });
    }

    if (modal) {
        modal.addEventListener('click', e => {
            if (e.target === modal) modal.classList.remove('active');
        });
    }
});
</script>

<script>
/* ---- Create Client Modal ---- */
document.addEventListener('DOMContentLoaded', function () {
    const createButtons = document.querySelectorAll('.ab-openCreateAdminClient');
    const createModal = document.getElementById('amAdminClientCreateModal');
    const closeCreateBtn = document.getElementById('closeAdminClientCreateModal');
    const createAvatarInput = document.getElementById('create_admin_client_profile_picture');
    const createAvatarPreview = document.getElementById('createAdminClientPreviewAvatar');

    createButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (createModal) createModal.style.display = 'flex';
        });
    });

    if (closeCreateBtn) {
        closeCreateBtn.addEventListener('click', () => {
            if (createModal) createModal.style.display = 'none';
        });
    }

    if (createModal) {
        createModal.addEventListener('click', e => {
            if (e.target === createModal) createModal.style.display = 'none';
        });
    }

    if (createAvatarInput && createAvatarPreview) {
        createAvatarPreview.addEventListener('click', () => createAvatarInput.click());
        createAvatarInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => createAvatarPreview.src = e.target.result;
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>

<script>
/* ---- Edit Client Modal ---- */
document.addEventListener('DOMContentLoaded', function () {
    const editButtons = document.querySelectorAll('.ab-editClient');
    const editModal = document.getElementById('amAdminClientEditModal');
    const closeBtn = document.getElementById('closeAdminClientEditModal');
    const avatarInput = document.getElementById('edit_admin_client_profile_picture');
    const avatarPreview = document.getElementById('editAdminClientPreviewAvatar');

    editButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (!editModal) return;

            const setValue = (id, value) => {
                const field = document.getElementById(id);
                if (field) field.value = value || '';
            };

            setValue('edit_admin_client_id', button.dataset.id);
            setValue('edit_admin_client_full_name', button.dataset.name);
            setValue('edit_admin_client_email', button.dataset.email);
            setValue('edit_admin_client_phone', button.dataset.phone);
            setValue('edit_admin_client_address', button.dataset.address);
            setValue('edit_admin_client_note', button.dataset.note || '');

            avatarPreview.src = button.dataset.avatar || "<?php echo esc_url(wp_upload_dir()['baseurl'] . '/2025/08/client-photo.jpg'); ?>";
            editModal.style.display = 'flex';
        });
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            if (editModal) editModal.style.display = 'none';
        });
    }

    if (editModal) {
        editModal.addEventListener('click', e => {
            if (e.target === editModal) editModal.style.display = 'none';
        });
    }

    if (avatarInput && avatarPreview) {
        avatarPreview.addEventListener('click', () => avatarInput.click());
        avatarInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => avatarPreview.src = e.target.result;
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>

<script>
/* ---- Client Details Modal (clickable client name) ---- */
document.addEventListener('DOMContentLoaded', function () {
    const clientLinks = document.querySelectorAll('.ab-viewClientDetails.clickable-name');
    const modal = document.getElementById('clientDetailsModal');
    const closeBtn = document.getElementById('closeClientDetailsModal');

    const modalAvatar = modal.querySelector('.client-avatar');
    const modalName   = modal.querySelector('.client-name');
    const modalTable  = modal.querySelector('.client-details-rt');

    clientLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.stopPropagation(); // extra safety
            const name    = this.dataset.name;
            const email   = this.dataset.email;
            const phone   = this.dataset.phone;
            const address = this.dataset.address;
            const company = this.dataset.company;
            const broker  = this.dataset.broker;
            const avatar  = this.dataset.avatar;

            modalAvatar.src = avatar;
            modalName.textContent = name;
            modalTable.innerHTML = `
                <tr><td>Client Name</td><td>${name}</td></tr>
                <tr><td>Email</td><td>${email}</td></tr>
                <tr><td>Phone Number</td><td>${phone}</td></tr>
                <tr><td>Address</td><td>${address}</td></tr>
                <tr><td>Company</td><td>${company}</td></tr>
                <tr><td>Broker ID</td><td>${broker}</td></tr>
            `;
            modal.style.display = 'flex';
        });
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', () => modal.style.display = 'none');
    }

    modal.addEventListener('click', e => {
        if (e.target === modal) modal.style.display = 'none';
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Select all delete icons by their title attribute
    const deleteButtons = document.querySelectorAll('.ab-action-icon[title="Delete"]');

    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            // Confirm before deleting the row
            if (confirm('Are you sure you want to remove this row temporarily?')) {
                const row = this.closest('tr'); // Find the closest table row
                if (row) {
                    // Optional fade-out effect before removing
                    row.style.transition = 'opacity 0.3s ease';
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 300); // Remove row after fade-out
                }
            }
        });
    });
});
</script>