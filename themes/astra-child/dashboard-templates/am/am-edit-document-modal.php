<!-- ==============================
     EDIT DOCUMENT MODAL
=============================== -->
<div id="am-edit-document-modal" class="clup-modal-overlay">
    <div class="clup-box">
        <button type="button" class="clup-close-btn">&times;</button>
        <h1 class="clup-title">Edit Document</h1><br>

        <form id="am-edit-document-form" class="clup-form" enctype="multipart/form-data">
            <input type="hidden" name="document_id" value="">

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
                        $current_user_id = get_current_user_id(); // বর্তমান ইউজারের ID

                        $doc_types = $wpdb->get_results(
                            $wpdb->prepare(
                                "SELECT id, type_name 
                                FROM {$wpdb->prefix}document_types 
                                WHERE deleted_at IS NULL 
                                AND created_by = %d
                                ORDER BY type_name ASC",
                                $current_user_id
                            )
                        );

                        if ($doc_types) {
                            foreach ($doc_types as $type) {
                                echo '<option value="' . esc_attr($type->id) . '">' . esc_html($type->type_name) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="clup-row-single">
                <div class="clup-field">
                    <label>Note (optional)</label>
                    <textarea name="note" rows="4" placeholder="Enter note about the document"></textarea>
                </div>
            </div>

            <div class="clup-upload-box">
                <div class="clup-upload-content">
                    <div class="clup-upload-icon">⬆</div>
                    <p>Replace File (optional)</p>
                    <span>Format: .jpeg, .png, .pdf & Max file size: 25 MB</span>

                    <!-- Current File Info -->
                    <div id="current-file-info" style="margin-bottom:10px; padding:8px; background:#f8f9fa; border-radius:4px; display:none;">
                        <small><strong>Current file:</strong> <a href="#" target="_blank" id="current-file-link"></a></small>
                    </div>

                    <button type="button" class="clup-browse">Browse New File</button>
                    <input type="file" id="edit-file-input" name="file_name" class="clup-file-input" accept=".jpeg,.jpg,.png,.pdf" style="display:none;">
                    <span id="edit-selected-file-name" style="display:block; margin-top:5px;"></span>
                </div>
            </div>

            <div class="clup-actions">
                <button type="submit" class="clup-btn clup-upload">Update</button>
            </div>
        </form>
    </div>
</div>
