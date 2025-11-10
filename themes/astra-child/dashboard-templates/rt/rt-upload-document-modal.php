<?php if (!defined('ABSPATH')) exit; ?>

<div id="cl-upload-document-modal" class="clup-modal-overlay">
    <div class="clup-box">
        <button type="button" class="clup-close-btn">&times;</button>
        <h1 class="clup-title">Upload Document</h1><br>

        <form id="upload-document-form" class="clup-form" enctype="multipart/form-data">
            <input type="hidden" name="document_id" value="">
            <input type="hidden" name="client_id" value="">
            <input type="hidden" name="properties_id" value="">

            <div class="clup-row-single">
                <div class="clup-field">
                    <label>Document Title</label>
                    <input type="text" name="title" placeholder="Enter title" required />
                </div>
            </div>

            <div class="clup-row-single">
                <div class="clup-field">
                    <label>Document Type</label>
                    <select name="type_id" required>
                        <option value="">Select Document Type</option>
                        <?php
                        global $wpdb;
                        $doc_types = $wpdb->get_results("SELECT id, type_name FROM {$wpdb->prefix}document_types WHERE deleted_at IS NULL ORDER BY type_name ASC");
                        if ($doc_types) {
                            foreach ($doc_types as $type) {
                                echo '<option value="' . esc_attr($type->id) . '">' . esc_html($type->type_name) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="clup-upload-box">
                <div class="clup-upload-content">
                    <div class="clup-upload-icon">⬆</div>
                    <p>Upload File</p>
                    <span>Format: .jpeg, .png, .pdf & Max file size: 25 MB</span>
                    <button type="button" class="clup-browse">Browse</button>
                    <input type="file" name="file_name" class="clup-file-input" accept=".jpeg,.jpg,.png,.pdf" style="display:none;">
                    <span id="selected-file-name" style="display:block; margin-top:5px;"></span>
                </div>
            </div>

            <div class="clup-actions">
                <button type="submit" class="clup-btn clup-upload">Save</button>
            </div>
        </form>
    </div>
</div>


<style>
.clup-modal-overlay {
    display: none;               /* hidden by default */
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;

    justify-content: center;     /* horizontal center */
    align-items: center;         /* vertical center */
    padding: 20px;
}

.clup-modal-overlay.show {
    display: flex !important;    /* flex display to center modal */
}

/* =========================
   Modal Box
========================= */
.clup-box {
    background: #fff;
    width: 100%;
    max-width: 650px;
    border-radius: 10px;
    padding: 25px 30px;
    position: relative;
    animation: fadeInUp 0.3s ease forwards;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

/* Animation */
@keyframes fadeInUp {
    from { transform: translateY(30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* =========================
   Close Button
========================= */
.clup-close-btn {
    position: absolute;
    right: 15px;
    top: 15px;
    border: none;
    background: transparent;
    font-size: 26px;
    font-weight: bold;
    cursor: pointer;
    color: #333;
    transition: color 0.2s ease;
}
.clup-close-btn:hover {
    color: #e63946;
}

/* =========================
   Title
========================= */
.clup-title {
    font-size: 1.375rem !important;
    font-weight: bold;
    color: #222;
    margin-bottom: 20px;
}

/* =========================
   Form Layout
========================= */
.clup-form {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.clup-row-single {
    display: flex;
    gap: 10px;
}

.clup-row-single .clup-field {
    flex: 1;
}

.clup-field label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 6px;
    color: #444;
}

.clup-field input,
.clup-field select {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
}

/* =========================
   File Upload
========================= */
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
    transition: background 0.2s ease;
}
.clup-browse:hover {
    background: #222;
}

/* =========================
   Form Actions
========================= */
.clup-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 15px;
}

.clup-btn {
    padding: 10px 20px !important;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: background 0.2s ease, color 0.2s ease;
}

.clup-cancel {
    background: #ddd;
    color: #333;
}
.clup-cancel:hover {
    background: #ccc;
}

.clup-upload {
    background: #2f64e2 !important;
    color: #fff !important;
}
.clup-upload:hover {
    background: #2a5aca !important;
}

/* =========================
   Mobile Responsive
========================= */
@media (max-width: 600px) {
    .clup-box {
        padding: 20px;
    }

    .clup-row-single {
        flex-direction: column;
    }
}

</style>