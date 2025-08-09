<!-- Edit Property Modal -->
<div class="property-modal-overlay" id="propertyEditModal" style="display: none;" role="dialog" aria-labelledby="editPropertyTitle">
  <div class="property-modal">
    
    <!-- Modal Header -->
    <div class="modal-header">
      <h2 id="editPropertyTitle">Edit Property</h2>
    </div>

    <!-- Edit Property Form -->
    <form method="post" enctype="multipart/form-data">
      
      <div class="form-grid">
        <div class="form-group">
          <label for="propertyName">Property Name</label>
          <input type="text" id="propertyName" name="property_name" required>
        </div>
        <div class="form-group">
          <label for="propertyType">Type</label>
          <input type="text" id="propertyType" name="property_type">
        </div>
        <div class="form-group">
          <label for="propertyFacilities">Facilities</label>
          <input type="text" id="propertyFacilities" name="property_facilities">
        </div>
        <div class="form-group">
          <label for="propertyPrice">Price</label>
          <input type="text" id="propertyPrice" name="property_price">
        </div>
      </div>

      <div class="form-grid" style="margin-top: 15px;">
        <div class="form-group">
          <label for="propertyDescription">Description</label>
          <textarea id="propertyDescription" name="property_description" rows="4"></textarea>
        </div>
        <div class="form-group">
          <label style="visibility: hidden;">Upload</label>
          <div class="upload-box">
            <label for="imageUpload">
              Drop file or browse<br>
              <small>Format: .jpeg, .png &amp; Max file size: 25 MB</small>
            </label>
            <input type="file" id="imageUpload" name="property_image" accept="image/*">
          </div>
        </div>
      </div>

      <div class="section-title">Add Task</div>

      <div class="form-grid">
        <div class="form-group">
          <label for="docTitle">Document Title</label>
          <input type="text" id="docTitle" name="document_title">
        </div>
        <div class="form-group">
          <label for="docType">Document Type</label>
          <input type="text" id="docType" name="document_type">
        </div>
      </div>

      <div class="form-group" style="margin-top: 15px;">
        <label for="taskNotes">Task list / Notes</label>
        <textarea id="taskNotes" name="task_notes" rows="4" placeholder="Note"></textarea>
      </div>

      <div class="form-group" style="margin-top: 15px;">
        <div class="upload-box">
          <label for="docUpload">
            Document / File Upload<br>
            <small>Format: .jpeg, .png &amp; Max file size: 25 MB</small>
          </label>
          <input type="file" id="docUpload" name="document_file" accept=".png, .jpg, .jpeg">
        </div>
      </div>

      <div class="form-grid" style="margin-top: 15px;">
        <div class="form-group">
          <label for="clientSelect">Add Client</label>
          <select id="clientSelect" name="client">
            <option value="">Select Client</option>
            <option value="client_a">Client A</option>
            <option value="client_b">Client B</option>
          </select>
        </div>
        <div class="form-group">
          <label for="dueDate">Due Date</label>
          <input type="date" id="dueDate" name="due_date">
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="modal-footer">
        <button type="button" class="cancel-btn" onclick="closeEditModal()">Cancel</button>
        <button type="submit" class="save-btn">Update</button>
      </div>
    </form>
  </div>
</div>
