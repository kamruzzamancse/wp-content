<div class="ab-container">
    <div class="ab-table-header">
        <div class="ab-header-left">
            <h1 class="header-title">Realtors</h1>
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
                <button class="ab-btn ab-btn-create ab-openCreateRealtor">
                    <span class="dashicons dashicons-plus-alt"></span> Add Realtor
                </button>
            </div>
        </div>
    </div>

    <table class="realtors-table">
        <thead>
            <tr>
                <th class="ab-sl-column">#</th>
                <th>Realtor Name</th>
                <th>Email</th>
                <th>Phone Number</th>
                <th>Notes</th>
                <th class="ab-actions-column">Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Row 1 -->
            <tr>
                <td class="ab-sl-column" data-label="#SL">01</td>
                <td data-label="Realtor Name">
                    <span class="ab-viewClientDetails clickable-name" title="View">Afsana Hamid Mim</span>
                </td>
                <td data-label="Email">Support.info@gmail.com</td>
                <td data-label="Phone Number">999-888-666</td>
                <td data-label="Notes">Follow up next week</td>
                <td class="ab-actions-column" data-label="Actions">
                    <div class="ab-action-icons">
                        <span class="ab-action-icon ab-viewClientDetails" title="View">👁️</span>
                        <span 
                            class="ab-action-icon ab-editRealtor" 
                            title="Edit"
                            data-id="1"
                            data-name="Afsana Hamid Mim"
                            data-email="Support.info@gmail.com"
                            data-phone="999-888-666"
                            data-notes="Follow up next week"
                            data-company="Best Realty"
                            data-broker="BRK-2025-1234"
                            data-avatar="<?php echo esc_url(wp_upload_dir()['baseurl'] . '/2025/08/client-photo.jpg'); ?>">✏️
                        </span>
                        <span class="ab-action-icon" title="Delete">🗑️</span>
                    </div>
                </td>
            </tr>

            <!-- Row 2 -->
            <tr>
                <td class="ab-sl-column" data-label="#SL">02</td>
                <td data-label="Realtor Name">
                    <span class="ab-viewClientDetails clickable-name" title="View">Liam Anderson</span>
                </td>
                <td data-label="Email">liam.anderson@realtorspro.com</td>
                <td data-label="Phone Number">888-123-4567</td>
                <td data-label="Notes">Interested in premium listing</td>
                <td class="ab-actions-column" data-label="Actions">
                    <div class="ab-action-icons">
                        <span class="ab-action-icon ab-viewClientDetails" title="View">👁️</span>
                        <span 
                            class="ab-action-icon ab-editRealtor" 
                            title="Edit"
                            data-id="2"
                            data-name="Liam Anderson"
                            data-email="liam.anderson@realtorspro.com"
                            data-phone="888-123-4567"
                            data-notes="Interested in premium listing"
                            data-company="Real Estate Pro"
                            data-broker="BRK-2025-2234"
                            data-avatar="<?php echo esc_url(wp_upload_dir()['baseurl'] . '/2025/08/client-photo.jpg'); ?>">✏️
                        </span>
                        <span class="ab-action-icon" title="Delete">🗑️</span>
                    </div>
                </td>
            </tr>

            <!-- Row 3 -->
            <tr>
                <td class="ab-sl-column" data-label="#SL">03</td>
                <td data-label="Realtor Name">
                    <span class="ab-viewClientDetails clickable-name" title="View">Amelia Johnson</span>
                </td>
                <td data-label="Email">ameliaj@homesales.net</td>
                <td data-label="Phone Number">555-777-2323</td>
                <td data-label="Notes">Prefers email communication</td>
                <td class="ab-actions-column" data-label="Actions">
                    <div class="ab-action-icons">
                        <span class="ab-action-icon ab-viewClientDetails" title="View">👁️</span>
                        <span 
                            class="ab-action-icon ab-editRealtor" 
                            title="Edit"
                            data-id="3"
                            data-name="Amelia Johnson"
                            data-email="ameliaj@homesales.net"
                            data-phone="555-777-2323"
                            data-notes="Prefers email communication"
                            data-company="Home Sales LLC"
                            data-broker="BRK-2025-3234"
                            data-avatar="<?php echo esc_url(wp_upload_dir()['baseurl'] . '/2025/08/client-photo.jpg'); ?>">✏️
                        </span>
                        <span class="ab-action-icon" title="Delete">🗑️</span>
                    </div>
                </td>
            </tr>

            <!-- Row 4 -->
            <tr>
                <td class="ab-sl-column" data-label="#SL">04</td>
                <td data-label="Realtor Name">
                    <span class="ab-viewClientDetails clickable-name" title="View">Noah Wilson</span>
                </td>
                <td data-label="Email">noah.wilson@estateplus.org</td>
                <td data-label="Phone Number">321-654-9870</td>
                <td data-label="Notes">Interested in rentals</td>
                <td class="ab-actions-column" data-label="Actions">
                    <div class="ab-action-icons">
                        <span class="ab-action-icon ab-viewClientDetails" title="View">👁️</span>
                        <span 
                            class="ab-action-icon ab-editRealtor" 
                            title="Edit"
                            data-id="4"
                            data-name="Noah Wilson"
                            data-email="noah.wilson@estateplus.org"
                            data-phone="321-654-9870"
                            data-notes="Interested in rentals"
                            data-company="Estate Plus"
                            data-broker="BRK-2025-4234"
                            data-avatar="<?php echo esc_url(wp_upload_dir()['baseurl'] . '/2025/08/client-photo.jpg'); ?>">✏️
                        </span>
                        <span class="ab-action-icon" title="Delete">🗑️</span>
                    </div>
                </td>
            </tr>

            <!-- Row 5 -->
            <tr>
                <td class="ab-sl-column" data-label="#SL">05</td>
                <td data-label="Realtor Name">
                    <span class="ab-viewClientDetails clickable-name" title="View">Emma Davis</span>
                </td>
                <td data-label="Email">emma.davis@realtymarket.io</td>
                <td data-label="Phone Number">707-555-9087</td>
                <td data-label="Notes">Needs virtual tour setup</td>
                <td class="ab-actions-column" data-label="Actions">
                    <div class="ab-action-icons">
                        <span class="ab-action-icon ab-viewClientDetails" title="View">👁️</span>
                        <span 
                            class="ab-action-icon ab-editRealtor" 
                            title="Edit"
                            data-id="5"
                            data-name="Emma Davis"
                            data-email="emma.davis@realtymarket.io"
                            data-phone="707-555-9087"
                            data-notes="Needs virtual tour setup"
                            data-company="Realty Market"
                            data-broker="BRK-2025-5234"
                            data-avatar="<?php echo esc_url(wp_upload_dir()['baseurl'] . '/2025/08/client-photo.jpg'); ?>">✏️
                        </span>
                        <span class="ab-action-icon" title="Delete">🗑️</span>
                    </div>
                </td>
            </tr>

            <!-- Row 6 -->
            <tr>
                <td class="ab-sl-column" data-label="#SL">06</td>
                <td data-label="Realtor Name">
                    <span class="ab-viewClientDetails clickable-name" title="View">William Moore</span>
                </td>
                <td data-label="Email">willmoore@prohomes.biz</td>
                <td data-label="Phone Number">800-333-4466</td>
                <td data-label="Notes">Wants advanced CRM features</td>
                <td class="ab-actions-column" data-label="Actions">
                    <div class="ab-action-icons">
                        <span class="ab-action-icon ab-viewClientDetails" title="View">👁️</span>
                        <span 
                            class="ab-action-icon ab-editRealtor" 
                            title="Edit"
                            data-id="6"
                            data-name="William Moore"
                            data-email="willmoore@prohomes.biz"
                            data-phone="800-333-4466"
                            data-notes="Wants advanced CRM features"
                            data-company="Pro Homes"
                            data-broker="BRK-2025-6234"
                            data-avatar="<?php echo esc_url(wp_upload_dir()['baseurl'] . '/2025/08/client-photo.jpg'); ?>">✏️
                        </span>
                        <span class="ab-action-icon" title="Delete">🗑️</span>
                    </div>
                </td>
            </tr>

            <!-- Row 7 -->
            <tr>
                <td class="ab-sl-column" data-label="#SL">07</td>
                <td data-label="Realtor Name">
                    <span class="ab-viewClientDetails clickable-name" title="View">Charlotte Lee</span>
                </td>
                <td data-label="Email">charlotte.lee@urbanestate.com</td>
                <td data-label="Phone Number">609-901-7890</td>
                <td data-label="Notes">Requested market analysis</td>
                <td class="ab-actions-column" data-label="Actions">
                    <div class="ab-action-icons">
                        <span class="ab-action-icon ab-viewClientDetails" title="View">👁️</span>
                        <span 
                            class="ab-action-icon ab-editRealtor" 
                            title="Edit"
                            data-id="7"
                            data-name="Charlotte Lee"
                            data-email="charlotte.lee@urbanestate.com"
                            data-phone="609-901-7890"
                            data-notes="Requested market analysis"
                            data-company="Urban Estate"
                            data-broker="BRK-2025-7234"
                            data-avatar="<?php echo esc_url(wp_upload_dir()['baseurl'] . '/2025/08/client-photo.jpg'); ?>">✏️
                        </span>
                        <span class="ab-action-icon" title="Delete">🗑️</span>
                    </div>
                </td>
            </tr>

            <!-- Row 8 -->
            <tr>
                <td class="ab-sl-column" data-label="#SL">08</td>
                <td data-label="Realtor Name">
                    <span class="ab-viewClientDetails clickable-name" title="View">Benjamin Harris</span>
                </td>
                <td data-label="Email">ben.harris@luxuryrealtors.co</td>
                <td data-label="Phone Number">444-222-9999</td>
                <td data-label="Notes">Focus on luxury properties</td>
                <td class="ab-actions-column" data-label="Actions">
                    <div class="ab-action-icons">
                        <span class="ab-action-icon ab-viewClientDetails" title="View">👁️</span>
                        <span 
                            class="ab-action-icon ab-editRealtor" 
                            title="Edit"
                            data-id="8"
                            data-name="Benjamin Harris"
                            data-email="ben.harris@luxuryrealtors.co"
                            data-phone="444-222-9999"
                            data-notes="Focus on luxury properties"
                            data-company="Luxury Realtors"
                            data-broker="BRK-2025-8234"
                            data-avatar="<?php echo esc_url(wp_upload_dir()['baseurl'] . '/2025/08/client-photo.jpg'); ?>">✏️
                        </span>
                        <span class="ab-action-icon" title="Delete">🗑️</span>
                    </div>
                </td>
            </tr>

            <!-- Row 9 -->
            <tr>
                <td class="ab-sl-column" data-label="#SL">09</td>
                <td data-label="Realtor Name">
                    <span class="ab-viewClientDetails clickable-name" title="View">Harper White</span>
                </td>
                <td data-label="Email">harper.white@primehomes.info</td>
                <td data-label="Phone Number">212-888-1234</td>
                <td data-label="Notes">Needs staging service info</td>
                <td class="ab-actions-column" data-label="Actions">
                    <div class="ab-action-icons">
                        <span class="ab-action-icon ab-viewClientDetails" title="View">👁️</span>
                        <span 
                            class="ab-action-icon ab-editRealtor" 
                            title="Edit"
                            data-id="9"
                            data-name="Harper White"
                            data-email="harper.white@primehomes.info"
                            data-phone="212-888-1234"
                            data-notes="Needs staging service info"
                            data-company="Prime Homes"
                            data-broker="BRK-2025-9234"
                            data-avatar="<?php echo esc_url(wp_upload_dir()['baseurl'] . '/2025/08/client-photo.jpg'); ?>">✏️
                        </span>
                        <span class="ab-action-icon" title="Delete">🗑️</span>
                    </div>
                </td>
            </tr>

            <!-- Row 10 -->
            <tr>
                <td class="ab-sl-column" data-label="#SL">10</td>
                <td data-label="Realtor Name">
                    <span class="ab-viewClientDetails clickable-name" title="View">Elijah Martin</span>
                </td>
                <td data-label="Email">elijah@martinrealtygroup.com</td>
                <td data-label="Phone Number">999-101-5050</td>
                <td data-label="Notes">Meeting scheduled next month</td>
                <td class="ab-actions-column" data-label="Actions">
                    <div class="ab-action-icons">
                        <span class="ab-action-icon ab-viewClientDetails" title="View">👁️</span>
                        <span 
                            class="ab-action-icon ab-editRealtor" 
                            title="Edit"
                            data-id="10"
                            data-name="Elijah Martin"
                            data-email="elijah@martinrealtygroup.com"
                            data-phone="999-101-5050"
                            data-notes="Meeting scheduled next month"
                            data-company="Martin Realty Group"
                            data-broker="BRK-2025-10234"
                            data-avatar="<?php echo esc_url(wp_upload_dir()['baseurl'] . '/2025/08/client-photo.jpg'); ?>">✏️
                        </span>
                        <span class="ab-action-icon" title="Delete">🗑️</span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

</div>

<?php
    include locate_template('dashboard-templates/am/am-realtor-view-modal.php');
    include locate_template('dashboard-templates/am/am-realtor-create-modal.php');
    include locate_template('dashboard-templates/am/am-realtor-edit-modal.php');
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
        max-width: none !important;
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

.realtors-table {
  border-collapse: separate !important;
  border-spacing: 0 !important;
  border-radius: 10px !important;
  overflow: hidden; /* Ensures the rounded corners clip the content */
}

.realtors-table thead th:first-child {
  border-top-left-radius: 10px !important;
}

.realtors-table thead th:last-child {
  border-top-right-radius: 10px !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const viewButtons = document.querySelectorAll('.ab-viewClientDetails');
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
document.addEventListener('DOMContentLoaded', function () {
    const createButtons = document.querySelectorAll('.ab-openCreateRealtor'); // Add this class to your "Create Realtor" button(s)
    const createModal = document.getElementById('amRealtorCreateModal');
    const closeCreateBtn = document.getElementById('closeRealtorCreateModal');
    const createAvatarInput = document.getElementById('create_realtor_profile_picture');
    const createAvatarPreview = document.getElementById('createPreviewAvatar');

    // Open modal
    createButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (createModal) createModal.style.display = 'flex';
        });
    });

    // Close on close button
    if (closeCreateBtn) {
        closeCreateBtn.addEventListener('click', () => {
            if (createModal) createModal.style.display = 'none';
        });
    }

    // Close on outside click
    if (createModal) {
        createModal.addEventListener('click', e => {
            if (e.target === createModal) {
                createModal.style.display = 'none';
            }
        });
    }

    // Handle avatar preview
    if (createAvatarInput && createAvatarPreview) {
        createAvatarInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    createAvatarPreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editButtons = document.querySelectorAll('.ab-editRealtor');
    const editModal = document.getElementById('amRealtorEditModal');
    const closeBtn = document.getElementById('closeRealtorEditModal');
    const avatarInput = document.getElementById('edit_realtor_profile_picture');
    const avatarPreview = document.getElementById('editPreviewAvatar');

    editButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (!editModal) return;

            // Populate the form fields
            document.getElementById('edit_realtor_id').value = button.dataset.id || '';
            document.getElementById('edit_realtor_full_name').value = button.dataset.name || '';
            document.getElementById('edit_realtor_email').value = button.dataset.email || '';
            document.getElementById('edit_realtor_phone').value = button.dataset.phone || '';
            document.getElementById('edit_realtor_address').value = button.dataset.address || '';
            document.getElementById('edit_realtor_company_name').value = button.dataset.company || '';
            document.getElementById('edit_realtor_broker_number').value = button.dataset.broker || '';

            avatarPreview.src = button.dataset.avatar || "<?php echo esc_url(wp_upload_dir()['baseurl'] . '/2025/08/client-photo.jpg'); ?>";

            editModal.style.display = 'flex';
        });
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            editModal.style.display = 'none';
        });
    }

    if (editModal) {
        editModal.addEventListener('click', e => {
            if (e.target === editModal) {
                editModal.style.display = 'none';
            }
        });
    }

    // Handle avatar change
    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    avatarPreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>


