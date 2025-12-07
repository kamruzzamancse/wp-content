<?php
if (!defined('ABSPATH')) exit;
global $wpdb;

// Upload base URL
$upload_baseurl = wp_upload_dir()['baseurl'];
?>

<!-- ================================
     Documents Section
================================ -->
<div class="cld-task-section" style="margin-top:30px;">
    <div class="cld-docs-wrapper">
        <div class="cld-docs-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
            <h2 class="header-title">Documents</h2>
            <button id="uploadDocBtn" class="btn-primary">+ Upload Document</button>
        </div>

        <div class="documents-controls">
            <label for="docRowsPerPage">Show:</label>
            <select id="docRowsPerPage">
                <option value="5" selected>5 rows</option>
                <option value="10">10 rows</option>
                <option value="25">25 rows</option>
            </select>
        </div>

        <table class="docs-table">
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>File</th>
                    <th>Note</th>
                    <th style="width:100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $current_user_id = get_current_user_id(); // Filter by current user
                $docs = $wpdb->get_results($wpdb->prepare("
                    SELECT d.*, t.type_name 
                    FROM {$wpdb->prefix}docs d
                    LEFT JOIN {$wpdb->prefix}document_types t ON d.type_id = t.id
                    WHERE d.deleted_at IS NULL AND d.created_by=%d
                    ORDER BY d.created_at DESC
                ", $current_user_id));

                if ($docs):
                    $doc_serial = 1;
                    foreach ($docs as $doc):
                        $file_name = !empty($doc->file_name) ? basename($doc->file_name) : '';
                        $file_url = !empty($doc->file_name) ? $upload_baseurl . '/' . $doc->file_name : '#';
                ?>
                    <tr data-id="<?php echo esc_attr($doc->id); ?>" data-type-id="<?php echo esc_attr($doc->type_id); ?>">
                        <td><?php echo $doc_serial++; ?></td>
                        <td><?php echo esc_html($doc->title); ?></td>
                        <td><?php echo esc_html($doc->type_name); ?></td>
                        <td>
                            <?php if($file_name): ?>
                                <a href="<?php echo esc_url($file_url); ?>" target="_blank"><?php echo esc_html($file_name); ?></a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html($doc->note); ?></td>
                        <td>
                            <?php if($file_name): ?>
                                <a href="<?php echo esc_url($file_url); ?>" download title="Download <?php echo esc_attr($file_name); ?>">⬇️</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                            <span class="edit-doc" title="Edit">✏️</span>
                            <span class="delete-doc" title="Delete">🗑️</span>
                        </td>
                    </tr>
                <?php
                    endforeach;
                else: ?>
                    <tr><td colspan="6" style="text-align:center;">No Documents Found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div id="docsPagination" class="pagination" style="display:flex; justify-content:flex-end; margin-top:10px;"></div>
    </div>
</div>

<!-- ================================
     Modals
================================ -->
<?php
include locate_template('dashboard-templates/am/am-upload-document-modal.php');
include locate_template('dashboard-templates/am/am-edit-document-modal.php');
?>

<style>
/* ==========================
   General Wrapper & Section
========================== */
.cld-task-section {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 1px 6px rgba(0,0,0,0.08);
    margin-bottom: 20px;
    width: 100%;
    box-sizing: border-box;
    overflow-x: auto;
}

/* ==========================
   Header (Title + Button)
========================== */
.cld-docs-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

#uploadDocBtn,
.btn-primary {
    background-color: #0073e6;
    color: #FFF!important;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    transition: background 0.2s ease;
}

#uploadDocBtn:hover,
.btn-primary:hover {
    background-color: #005bb5;
}

/* ==========================
   Show Rows Dropdown
========================== */
.documents-controls {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
    margin-bottom: 10px;
}

.documents-controls label {
    font-size: 14px;
}

#docRowsPerPage {
    padding: 5px 10px;
    border-radius: 6px;
    border: 1px solid #ddd;
    cursor: pointer;
    background: #fff;
}

/* ==========================
   Tables
========================== */
.docs-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    border: 1px solid #ddd;
    border-radius: 8px 8px 0 0;
    overflow: hidden;
    font-size: 14px;
}

.docs-table th,
.docs-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.docs-table th {
    background: #2271b1;
    color: #fff;
    font-weight: 600;
}

.docs-table td:last-child {
    display: flex;
    justify-content: center;
    gap: 10px;
}

/* ==========================
   Action Icons
========================== */
.edit-doc,
.delete-doc,
.edit-doc-type,
.delete-doc-type {
    cursor: pointer;
    transition: color 0.2s ease;
}

.edit-doc:hover,
.edit-doc-type:hover {
    color: #ffb400;
}

.delete-doc:hover,
.delete-doc-type:hover {
    color: #e63946;
}

/* ==========================
   Pagination
========================== */
.pagination {
    display: flex;
    justify-content: flex-end;
    margin-top: 10px;
    gap: 6px;
}

.page-btn {
    padding: 5px 10px;
    border: 1px solid #ddd;
    background: #fff;
    color: #000;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
}

.page-btn:hover {
    background: #2271b1;
    color: #fff;
    border-color: #2271b1;
}

.page-btn.active {
    background: #2271b1;
    color: #fff;
    border-color: #2271b1;
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
    background: rgba(0,0,0,0.45);
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
    background: #fff;
    width: 475px;
    max-width: 92%;
    border-radius: 12px;
    padding: 20px 28px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.2);
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
    margin-bottom: 15px;
    font-size: 22px;
    font-weight: 600;
    color: #222;
}

.clup-close-btn {
    position: absolute;
    top: 12px;
    right: 15px;
    font-size: 20px;
    cursor: pointer;
    transition: transform 0.2s;
}

.clup-close-btn:hover {
    transform: scale(1.15);
    color: #000;
}

/* ==========================
   Inputs & Textareas
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
    transition: border-color 0.2s;
}

.clup-box input:focus,
.clup-box select:focus,
.clup-box textarea:focus {
    border-color: #007BFF;
    outline: none;
}

/* ==========================
   Buttons inside Modal
========================== */
.clup-btn {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 15px;
    cursor: pointer;
    border: none;
    transition: background 0.2s, transform 0.2s;
}

.clup-save,
.clup-upload {
    background: #007BFF;
    color: #fff;
}

.clup-save:hover,
.clup-upload:hover {
    background: #005FCC;
    transform: translateY(-1px);
}

.clup-cancel {
    background: #e6e6e6;
    color: #333;
}

.clup-cancel:hover {
    background: #d5d5d5;
}

/* ==========================
   File Upload Box
========================== */
.clup-upload-box {
    border: 2px dashed #999;
    border-radius: 8px;
    padding: 25px;
    text-align: center;
    background: #fafafa;
    cursor: pointer;
    transition: background 0.2s ease, border-color 0.2s ease;
}

.clup-upload-box:hover {
    background: #f0f0f0;
    border-color: #777;
}

.clup-upload-icon {
    font-size: 30px;
    margin-bottom: 10px;
}

.clup-upload-content p {
    font-size: 15px;
    margin: 0 0 5px;
    font-weight: 600;
}

.clup-upload-content span {
    font-size: 13px;
    color: #777;
}

.clup-browse {
    margin-top: 12px;
    background: #444;
    color: #fff;
    padding: 8px 14px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.2s;
}

.clup-browse:hover {
    background: #222;
}

#docRowsPerPage {
    width: 100px;
}

/* ==========================
   Responsive Tables
========================== */
@media(max-width:768px){
    .docs-table thead { display:none; }
    .docs-table tr {
        display:block;
        margin-bottom:15px;
        border:1px solid #ddd;
        border-radius:8px;
        padding:10px;
    }
    .docs-table td {
        display:block;
        padding-left:40%;
        position:relative;
        border-bottom:1px solid #eee;
        text-align: left;
    }
    .docs-table td:last-child {
        justify-content:flex-start;
        border-bottom:none;
    }
    .docs-table td::before {
        content:attr(data-label);
        position:absolute;
        left:10px;
        width:35%;
        font-weight:600;
        font-size:12px;
    }
}

</style>
