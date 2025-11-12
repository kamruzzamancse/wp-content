<!-- Document Types Management -->
<div class="cld-doc-types-section">
    <div class="cld-doc-types-header">
        <h2 class="header-title">Document Types</h2>
        <button id="addDocTypeBtn" class="btn-primary">+ Add Type</button>
    </div>

    <table class="doc-types-table">
        <thead>
            <tr>
                <th style="width:50px;">#</th>
                <th>Type Name</th>
                <th style="width:120px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            global $wpdb;
            $doc_types = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}document_types WHERE deleted_at IS NULL ORDER BY created_at DESC");
            if ($doc_types):
                foreach ($doc_types as $index => $type): ?>
                    <tr data-id="<?php echo esc_attr($type->id); ?>">
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo esc_html($type->type_name); ?></td>
                        <td>
                            <span class="edit-doc-type" title="Edit">✏️</span>
                            <span class="delete-doc-type" title="Delete">🗑️</span>
                        </td>
                    </tr>
                <?php endforeach;
            else: ?>
                <tr><td colspan="3" style="text-align:center;">No Document Types Found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="cld-task-section">
    <div class="cld-task-header">
        <h2 class="header-title">Documents</h2>
    </div>

    <div class="documents-section">
        <table class="documents-table">
            <thead>
                <tr>
                    <th style="width:50px; background:#2271b1; color:#fff;">#</th>
                    <th style="background:#2271b1; color:#fff;">Document Title</th>
                    <th style="background:#2271b1; color:#fff;">Document Type</th>
                    <th style="background:#2271b1; color:#fff;">Document</th>
                    <th style="background:#2271b1; color:#fff;">Assigned/Replied</th>
                    <th style="width:120px; background:#2271b1; color:#fff;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                global $wpdb;
                $table_docs     = $wpdb->prefix . 'documents';
                $table_types    = $wpdb->prefix . 'document_types';
                $assigned_table = $wpdb->prefix . 'assigned_tasks';

                $documents = $wpdb->get_results("
                    SELECT d.id, d.title, d.file_name, d.type_id, d.doc_type, dt.type_name
                    FROM $table_docs d
                    LEFT JOIN $table_types dt ON d.type_id = dt.id
                    WHERE d.deleted_at IS NULL
                    ORDER BY d.created_at DESC
                ");

                if ($documents):
                    foreach ($documents as $index => $doc):

                        // File path & URL fix
                        $upload_dir = wp_upload_dir();
                        $file_path  = trailingslashit($upload_dir['basedir']) . ltrim($doc->file_name, '/');
                        $file_url   = trailingslashit($upload_dir['baseurl']) . ltrim($doc->file_name, '/');
                        $file_name  = basename($doc->file_name);

                        // Assigned/Replied status
                        $assigned_status = $doc->doc_type ?: 'Assigned';
                        $assignment = $wpdb->get_row($wpdb->prepare("
                            SELECT created_at, updated_at 
                            FROM $assigned_table
                            WHERE document_id=%d AND deleted_at IS NULL
                            ORDER BY id DESC
                            LIMIT 1
                        ", $doc->id));

                        if ($assignment) {
                            $assigned_date = $assignment->created_at;
                            $reply_date    = $assignment->updated_at;
                            if ($reply_date && $reply_date !== $assigned_date) {
                                $assigned_status = "Replied on " . date('d M Y', strtotime($reply_date));
                            } else {
                                $assigned_status = "Assigned on " . date('d M Y', strtotime($assigned_date));
                            }
                        }
                        ?>
                        <tr data-id="<?php echo esc_attr($doc->id); ?>">
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo esc_html($doc->title); ?></td>
                            <td data-type-id="<?php echo esc_attr($doc->type_id); ?>">
                                <?php echo esc_html($doc->type_name); ?>
                            </td>
                            <td>
                                <?php
                                $file_url  = $doc->file_name;
                                $file_name = basename($doc->file_name);

                                // Check file exists locally
                                $upload_dir = wp_upload_dir();
                                $file_path  = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $file_url);

                                if (file_exists($file_path)) : ?>
                                    <a href="<?php echo esc_url($file_url); ?>" target="_blank">
                                        <?php echo esc_html($file_name); ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color:red;">File missing</span>
                                <?php endif; ?>
                            </td>

                            <td><?php echo esc_html($assigned_status); ?></td>
                            <td>
                                <?php if (file_exists($file_path)): ?>
                                    <a href="<?php echo esc_url($file_url); ?>" download="<?php echo esc_attr($file_name); ?>"
                                       class="download-doc" title="Download" style="cursor:pointer; margin-right:5px;">⬇️</a>
                                <?php endif; ?>
                                <!-- <span class="edit-doc" title="Edit" style="cursor:pointer; margin-right:5px;">✏️</span>
                                <span class="delete-doc" title="Delete" style="cursor:pointer;">🗑️</span> -->
                            </td>
                        </tr>
                    <?php endforeach;
                else: ?>
                    <tr><td colspan="6" style="text-align:center;">No Documents Found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<?php 
    include locate_template('dashboard-templates/rt/rt-upload-document-modal.php');
    include locate_template('dashboard-templates/rt/rt-document-type-modal.php');
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Open Upload Document Modal
    const uploadButtons = document.querySelectorAll('.cld-upload-btn');
    uploadButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const modalId = btn.dataset.modal;
            const modal = document.getElementById(modalId);
            if (modal) modal.classList.add('show');

            // Reset form for new document
            const form = modal.querySelector('form');
            if(form) form.reset();
            const hiddenInput = form.querySelector('[name="document_id"]');
            if(hiddenInput) hiddenInput.value = '';
        });
    });

    // Close modal
    const closeButtons = document.querySelectorAll('.clup-close-btn');
    closeButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            btn.closest('.clup-modal-overlay').classList.remove('show');
        });
    });

    // File browse
    const browseButtons = document.querySelectorAll('.clup-browse');
    browseButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const fileInput = btn.parentElement.querySelector('.clup-file-input');
            if(fileInput) fileInput.click();
        });
    });
});
</script>

<style>
/* Container */
.cld-task-section {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    margin-bottom: 20px;
    width: 100%;
    box-sizing: border-box;
    overflow-x: hidden;
}

/* Header */
.cld-task-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.cld-upload-btn {
    background: #fff;
    border: 1px solid #0073e6;
    padding: 6px 14px;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
    color: #0073e6;
    display: flex;
    align-items: center;
    gap: 6px;
}

.cld-upload-btn:hover { 
    color: #FFF!important; 
    background: #0073e6; 
}

/* Dashboard Cards Grid */
.stats-grid {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.stat-card {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    width: 100%;
    max-width: 200px;
    padding: 20px;
    background: #f5f5f5;
    border-radius: 8px;
    text-decoration: none;
    color: #333;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    cursor: pointer;
}

.stat-card:hover { 
    background: #FFF; 
    box-shadow: 0 1px 3px rgba(0,0,0,0.05); 
}

.stat-card.active { 
    background:#2271b1; 
    color:#fff; 
}

.stat-card.active h3 { 
    color:#fff; 
}

.stat-card h3 {
    font-size: 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
}

/* Documents Table */
.documents-section { 
    margin-top: 30px; 
    overflow-x: auto; 
}

.documents-table {
    width: 100%;
    min-width: 600px;
    border-collapse: separate;
    border-spacing: 0;
    border: 1px solid #ddd;
    border-radius: 10px 10px 0 0;
    overflow: hidden;
}

/* Apply radius to first and last header cells */
.documents-table thead th:first-child {
    border-top-left-radius: 10px;
}

.documents-table thead th:last-child {
    border-top-right-radius: 10px;
}

.documents-table th, .documents-table td {
    padding: 12px 15px;
    text-align: left;
    font-size: 14px;
}

.documents-table th { 
    font-weight: 600; 
}

.doc-action {
    font-size: 14px;
    margin-right: 5px;
    text-decoration: none;
    cursor: pointer;
}

.doc-action.download { 
    color: #2f64e2; 
}

.doc-action.edit { 
    color: #ffb400; 
}

.doc-action.delete { 
    color: #e63946; 
}

.doc-action:hover { 
    text-decoration: underline; 
}

/* Tablet Responsive */
@media (max-width: 768px) {
    .stats-grid { 
        justify-content: center; 
        gap: 15px; 
    }
    
    /* Table Responsive Styles */
    .documents-section {
        overflow-x: visible;
    }
    
    .documents-table { 
        min-width: auto;
        border: none;
        border-radius: 0;
        background: transparent;
    }
    
    .documents-table thead { 
        display: none; 
    }
    
    .documents-table, 
    .documents-table tbody, 
    .documents-table tr, 
    .documents-table td { 
        display: block; 
        width: 100% !important;
    }
    
    .documents-table tbody {
        display: block;
        width: 100%;
    }
    
    .documents-table tr { 
        margin-bottom: 15px; 
        border: 1px solid #ddd; 
        border-radius: 8px; 
        padding: 10px; 
        background: #fff; 
        position: relative;
        box-sizing: border-box;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .documents-table td { 
        padding: 10px 10px 10px 45%; 
        border: none; 
        border-bottom: 1px solid #eee; 
        position: relative; 
        text-align: left; 
        min-height: 20px;
        box-sizing: border-box;
    }
    
    .documents-table td:last-child { 
        border-bottom: none; 
    }
    
    .documents-table td::before { 
        content: attr(data-label); 
        position: absolute; 
        left: 10px; 
        width: 40%; 
        padding-right: 10px; 
        white-space: nowrap; 
        text-align: left; 
        font-weight: 600; 
        color: #333; 
    }
    
    /* Special handling for Actions cell */
    .documents-table td[data-label="Actions"] {
        padding: 10px;
        text-align: center;
        border-bottom: none;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
    }
    
    .documents-table td[data-label="Actions"]::before {
        display: none;
    }
    
    .doc-action {
        margin: 0 5px;
        font-size: 16px;
    }
}

/* Mobile Responsive */
@media (max-width: 480px) {
    .cld-task-header { 
        flex-direction: column; 
        gap: 15px; 
        align-items: flex-start; 
    }
    
    .cld-upload-btn { 
        align-self: stretch; 
        justify-content: center; 
    }
    
    .stat-card { 
        max-width: 100%; 
        padding: 15px; 
    }
    
    .cld-task-section {
        padding: 10px;
    }
    
    /* Mobile Table Adjustments */
    .documents-table td {
        padding-left: 40%;
        font-size: 13px;
    }
    
    .documents-table td::before {
        width: 35%;
        font-size: 12px;
    }
    
    .doc-action {
        font-size: 16px;
        margin: 0 3px;
    }
}
</style>

<style>
.cld-doc-types-section {
    background: #fff;              /* match Documents section background */
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    max-width: 500px;             /* max width */
    width: 100%;
    box-sizing: border-box;
    overflow-x: auto;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

/* Header */
.cld-doc-types-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

/* Table */
.doc-types-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    border: 1px solid #ddd;
    border-radius: 10px 10px 0 0; /* top-left and top-right radius like Documents table */
    overflow: hidden;
}

/* Table headers */
.doc-types-table th, .doc-types-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #ddd; /* bottom border */
    text-align: left;
    font-size: 14px;
}

/* Header styling */
.doc-types-table th {
    background: #2271b1;
    color: #fff;
    font-weight: 600;
}

/* Rounded corners for first and last headers */
.doc-types-table th:first-child {
    border-top-left-radius: 10px;
}
.doc-types-table th:last-child {
    border-top-right-radius: 10px;
}

/* Bottom border for last row */
.doc-types-table tr:last-child td {
    border-bottom: 1px solid #ddd;
}

/* Action buttons */
.edit-doc-type, .delete-doc-type {
    cursor: pointer;
    margin-right: 5px;
}
.edit-doc-type:hover { color: #ffb400; }
.delete-doc-type:hover { color: #e63946; }

/* ===============================
   Responsive - Mobile View
================================= */
@media (max-width: 768px) {
    .doc-types-table {
        border: none;
        border-radius: 0;
    }

    .doc-types-table thead {
        display: none; /* hide headers */
    }

    .doc-types-table, 
    .doc-types-table tbody, 
    .doc-types-table tr, 
    .doc-types-table td {
        display: block;
        width: 100% !important;
    }

    .doc-types-table tr {
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 10px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        box-sizing: border-box;
        position: relative;
    }

    .doc-types-table td {
        padding: 10px 10px 10px 45%;
        border: none;
        border-bottom: 1px solid #eee;
        position: relative;
        min-height: 20px;
        box-sizing: border-box;
    }

    .doc-types-table td:last-child {
        border-bottom: none;
    }

    .doc-types-table td::before {
        content: attr(data-label);
        position: absolute;
        left: 10px;
        width: 40%;
        padding-right: 10px;
        white-space: nowrap;
        font-weight: 600;
        color: #333;
    }

    /* Actions cell special handling */
    .doc-types-table td[data-label="Actions"] {
        padding: 10px;
        display: flex;
        justify-content: flex-start;
        align-items: center;
        gap: 10px;
    }

    .doc-types-table td[data-label="Actions"]::before {
        display: none;
    }

    .edit-doc-type, .delete-doc-type {
        font-size: 16px;
        margin: 0 5px 0 0;
    }
}

/* ===============================
   Keep your existing .btn-primary intact
================================= */
.btn-primary {
    padding-top: 10px;
    padding-right: 20px;
    padding-bottom: 10px;
    padding-left: 20px;
    color: #FFF!important;
}

</style>