<!-- Property Modal -->
<div class="property-modal-overlay" id="propertyCreateModal" style="display: none;">
  <div class="property-modal">
    <h2>Create Property</h2>

    <div class="form-grid">
      <div class="form-group">
        <label>Property Name</label>
        <input type="text">
      </div>
      <div class="form-group">
        <label>Type</label>
        <input type="text">
      </div>
      <div class="form-group">
        <label>Facilities</label>
        <input type="text">
      </div>
      <div class="form-group">
        <label>Price</label>
        <input type="text">
      </div>
    </div>

    <div class="form-grid" style="margin-top: 15px;">
      <div class="form-group">
        <label>Description</label>
        <textarea rows="4"></textarea>
      </div>
      <div class="form-group">
        <label style="visibility: hidden;">Upload</label>
        <div class="upload-box">
          <label for="imageUpload">Drop file or browse<br><small>Format: .jpeg, .png & Max file size: 25 MB</small></label>
          <input type="file" id="imageUpload" accept="image/*">
        </div>
      </div>
    </div>

    <div class="section-title">Add Task</div>

    <div class="form-grid">
      <div class="form-group">
        <label>Document Title</label>
        <input type="text">
      </div>
      <div class="form-group">
        <label>Document Type</label>
        <input type="text">
      </div>
    </div>

    <div class="form-group" style="margin-top: 15px;">
      <label>Task list/ Notes</label>
      <textarea rows="4" placeholder="Note"></textarea>
    </div>

    <div class="form-group" style="margin-top: 15px;">
      <div class="upload-box">
        <label for="docUpload">Document/ File Upload<br><small>Format: .jpeg, .png & Max file size: 25 MB</small></label>
        <input type="file" id="docUpload" accept=".png, .jpg, .jpeg">
      </div>
    </div>

    <div class="form-grid" style="margin-top: 15px;">
      <div class="form-group">
        <label>Add Client</label>
        <select>
          <option>Select Client</option>
          <option>Client A</option>
          <option>Client B</option>
        </select>
      </div>
      <div class="form-group">
        <label>Due Date</label>
        <input type="date">
      </div>
    </div>

    <div class="modal-footer">
      <button class="cancel-btn" onclick="closeCreateModal()">Cancel</button>
      <button class="save-btn">Save</button>
    </div>
  </div>
</div>