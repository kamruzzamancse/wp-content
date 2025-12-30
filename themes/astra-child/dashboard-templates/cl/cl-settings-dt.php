<?php
if (!defined('ABSPATH')) exit;
global $wpdb;
?>

<!-- ================================
     Document Types Section
================================ -->
<div class="cld-task-section">

    <div class="cld-doc-types-wrapper">
        <div class="cld-doc-types-header">
            <h2 class="header-title">📂 Document Types</h2>
            <button id="addDocTypeBtn" class="btn-primary">+ Add Type</button>
        </div>

        <!-- Controls -->
        <div class="doc-types-controls">
            <label for="docTypeRowsPerPage">Show:</label>
            <select id="docTypeRowsPerPage">
                <option value="5" selected>5 rows</option>
                <option value="10">10 rows</option>
                <option value="25">25 rows</option>
            </select>
        </div>

        <!-- Document Types Table -->
        <table class="doc-types-table">
            <thead>
                <tr>
                    <th style="width:50px;">#</th>
                    <th>Document Type</th>
                    <th style="width:80px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $doc_types = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}document_types WHERE deleted_at IS NULL ORDER BY created_at DESC");
                if ($doc_types):
                    $serial = 1;
                    foreach ($doc_types as $type):
                        if (!$type) continue;
                ?>
                    <tr data-id="<?php echo esc_attr($type->id); ?>">
                        <td data-label="#"><?php echo $serial++; ?></td>
                        <td data-label="Document Type"><?php echo esc_html($type->type_name); ?></td>
                        <td data-label="Actions">
                            <span class="edit-doc-type" title="Edit">✏️</span>
                            <span class="delete-doc-type" title="Delete">🗑️</span>
                        </td>
                    </tr>
                <?php
                    endforeach;
                else: ?>
                    <tr><td colspan="3" style="text-align:center;">No Document Types Found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div id="docTypesPagination" class="pagination" style="display:flex; justify-content:flex-end; margin-top:10px;"></div>
    </div>

</div>

<!-- ================================
     Document Type Modal
================================ -->
<?php
// Include only document type modal file
include locate_template('dashboard-templates/am/am-document-type-modal.php');
?>

<style>
/* ==========================
   General Wrapper & Section
========================== */
.cld-task-section {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    margin-bottom: 20px;
    width: 100%;
    box-sizing: border-box;
    overflow-x: auto;
}

/* ==========================
   Header (Title + Button)
========================== */
.cld-doc-types-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.btn-primary {
    background-color: #0073e6;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    transition: background-color 0.2s ease;
}

.btn-primary:hover {
    background-color: #005bb5;
}

/* ==========================
   Show Rows Dropdown (Right-aligned)
========================== */
.doc-types-controls {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 5px;
    margin-bottom: 10px;
}

.doc-types-controls label {
    font-size: 14px;
    white-space: nowrap;
}

#docTypeRowsPerPage {
    width: 100px;
    padding: 5px 10px;
    border-radius: 6px;
    border: 1px solid #ddd;
    cursor: pointer;
    background: #fff;
    font-size: 14px;
}

/* ==========================
   Document Types Table
========================== */
.doc-types-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    border: 1px solid #ddd;
    border-radius: 10px 10px 0 0;
    overflow: hidden;
    margin-bottom: 20px;
}

.doc-types-table th,
.doc-types-table td {
    padding: 12px 15px;
    font-size: 14px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.doc-types-table th {
    background: #2271b1;
    color: #fff;
    font-weight: 600;
}

.doc-types-table td:last-child {
    display: flex;
    justify-content: flex-start;
    gap: 15px;
}

/* ==========================
   Actions
========================== */
.edit-doc-type,
.delete-doc-type {
    cursor: pointer;
    font-size: 16px;
    transition: all 0.2s ease;
    display: inline-block;
}

.edit-doc-type:hover {
    color: #ffb400;
    transform: scale(1.2);
}

.delete-doc-type:hover {
    color: #e63946;
    transform: scale(1.2);
}

/* ==========================
   Modal Overlay & Box
========================== */
.clup-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.45);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    opacity: 0;
    visibility: hidden;
    backdrop-filter: blur(3px);
    transition: opacity 0.3s ease, visibility 0.3s ease;
}

.clup-modal-overlay.show {
    display: flex;
    opacity: 1;
    visibility: visible;
}

.clup-box {
    background: #ffffff;
    width: 475px;
    max-width: 92%;
    border-radius: 12px;
    padding: 20px 28px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.20);
    animation: modalPop 0.25s ease-out;
    position: relative;
}

@keyframes modalPop {
    from { transform: scale(0.85); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

.clup-box h2,
.clup-title {
    margin-top: 0;
    margin-bottom: 20px;
    font-size: 22px;
    font-weight: 600;
    color: #222;
}

.clup-close-btn {
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 24px;
    cursor: pointer;
    color: #666;
    transition: 0.2s;
    background: none;
    border: none;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.clup-close-btn:hover {
    color: #000;
    transform: scale(1.15);
}

/* ==========================
   Inputs
========================== */
.clup-box input[type="text"],
.clup-box input[type="email"],
.clup-box input[type="number"],
.clup-box select,
.clup-box textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    margin-bottom: 15px;
    font-size: 15px;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.clup-box input:focus,
.clup-box select:focus,
.clup-box textarea:focus {
    border-color: #2271b1;
    outline: none;
    box-shadow: 0 0 0 3px rgba(34, 113, 177, 0.1);
}

/* ==========================
   Buttons inside Modal
========================== */
.clup-btn {
    padding: 10px 20px;
    border-radius: 6px;
    font-size: 15px;
    cursor: pointer;
    border: none;
    transition: all 0.2s ease;
    font-weight: 500;
}

.clup-save,
.clup-upload {
    background: #2271b1;
    color: #fff;
}

.clup-save:hover,
.clup-upload:hover {
    background: #1a5d9a;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.clup-cancel {
    background: #e6e6e6;
    color: #333;
}

.clup-cancel:hover {
    background: #d5d5d5;
    transform: translateY(-1px);
}

/* ==========================
   Pagination Styling
========================== */
#docTypesPagination {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 20px;
    gap: 5px;
    flex-wrap: wrap;
}

.pagination-btn {
    padding: 6px 12px;
    border: 1px solid #ddd;
    background: #fff;
    color: #333;
    cursor: pointer;
    border-radius: 4px;
    font-size: 14px;
    transition: all 0.2s ease;
    min-width: 40px;
    text-align: center;
    text-decoration: none;
}

.pagination-btn:hover:not(:disabled) {
    background: #f5f5f5;
    border-color: #999;
}

.pagination-btn.active {
    background: #2271b1;
    color: #fff;
    border-color: #2271b1;
    font-weight: bold;
}

.pagination-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.pagination-dots {
    padding: 6px 8px;
    color: #666;
    user-select: none;
}

.loading-spinner {
    display: inline-block;
    padding: 20px;
    color: #2271b1;
    animation: pulse 1.5s infinite;
    text-align: center;
    width: 100%;
}

#addDocTypeBtn {
    color:#FFF!important;
}

@keyframes pulse {
    0% { opacity: 0.6; }
    50% { opacity: 1; }
    100% { opacity: 0.6; }
}

/* ==========================
   Responsive Table
========================== */
@media(max-width: 768px){
    .cld-task-section {
        padding: 15px;
    }
    
    .cld-doc-types-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .doc-types-controls {
        width: 100%;
        justify-content: flex-start;
    }
    
    .doc-types-table thead { 
        display: none; 
    }
    
    .doc-types-table tr {
        display: block;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 10px;
        background: #f9f9f9;
    }

    .doc-types-table td {
        display: block;
        padding: 10px 10px 10px 40%;
        position: relative;
        border: none;
        border-bottom: 1px solid #eee;
    }

    .doc-types-table td:last-child {
        justify-content: flex-start;
        border-bottom: none;
        padding-left: 10px;
        display: flex;
        gap: 20px;
        padding-top: 15px;
    }

    .doc-types-table td::before {
        content: attr(data-label);
        position: absolute;
        left: 10px;
        width: 35%;
        font-weight: 600;
        font-size: 12px;
        color: #555;
    }
    
    .clup-box {
        padding: 15px 20px;
    }
    
    .clup-close-btn {
        top: 10px;
        right: 15px;
    }
}

/* ==========================
   Small Screen Responsive
========================== */
@media(max-width: 480px){
    
    .btn-primary {
        padding: 8px 16px;
        font-size: 13px;
    }
    
    .doc-types-table td {
        padding-left: 45%;
    }
    
    .doc-types-table td::before {
        width: 40%;
    }
    
    .clup-box {
        width: 95%;
        padding: 15px;
    }
}
</style>