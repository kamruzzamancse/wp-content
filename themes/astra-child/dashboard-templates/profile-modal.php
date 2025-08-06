<!-- Profile Modal -->
<div class="modal-overlay" id="profileModal">
    <div class="modal">
        <?php $current_user = wp_get_current_user(); ?>
        <img src="<?php echo esc_url(get_avatar_url($current_user->ID)); ?>" alt="Profile">
        <h3><?php echo esc_html($current_user->display_name); ?></h3>
        <p><?php echo esc_html($current_user->user_email); ?></p>

        <div class="modal-buttons">
            <button onclick="location.href='/profile'">View Profile</button>
            <button onclick="location.href='/edit-profile'">Edit Profile</button>
            <button onclick="location.href='<?php echo esc_url(wp_logout_url(home_url('/login/'))); ?>'">Log out</button>
        </div>
    </div>
</div>

<!-- Document Upload Modal -->
<div id="documentUploadModal" class="document-modal">
    <div class="document-modal-content">
        <div class="document-modal-header">
            <h2>Upload Document</h2>
        </div>
        
        <form id="documentUploadForm" class="document-upload-form">
            <div class="form-row-duo">
                <div class="form-group left-align">
                    <label for="documentTitle">Document Title</label>
                    <input type="text" id="documentTitle" name="documentTitle" required>
                </div>
                
                <div class="form-group right-align">
                    <label for="documentType">Document Type</label>
                    <input type="text" id="documentType" name="documentType" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="taskNotes">Task list/Notes</label>
                <textarea id="taskNotes" name="taskNotes" rows="4"></textarea>
            </div>
            
            <div class="form-group">
                <label>Document/File</label>
                <div class="file-upload-wrapper">
                    <input type="file" id="documentFile" name="documentFile" required>
                    <label for="documentFile" class="file-upload-label">
                        <span class="dashicons dashicons-upload"></span>
                        <span>Choose file</span>
                    </label>
                    <span class="file-name">No file chosen</span>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group left-align">
                    <label for="clientList">Client</label>
                    <select id="clientList" name="clientList">
                        <option value="">Select a client</option>
                        <option value="client1">John Smith</option>
                        <option value="client2">Sarah Johnson</option>
                    </select>
                </div>
                
                <div class="form-group right-align">
                    <label for="dueDate">Due Date</label>
                    <input type="date" id="dueDate" name="dueDate">
                </div>
            </div>
            
            <div class="form-actions center-align">
                <button type="button" class="cancel-btn">Cancel</button>
                <button type="submit" class="upload-btn">Upload</button>
            </div>
        </form>
    </div>
</div>