<!-- Add Document Type Modal -->
<div id="cl-add-doc-type-modal" class="clup-modal-overlay">
  <div class="clup-box">
    <button class="clup-close-btn">&times;</button>
    <h1 class="clup-title">Add Document Type</h1>

    <form id="add-doc-type-form" class="clup-form">
      <div class="clup-row-single">
        <div class="clup-field">
          <label>Document Type Name</label>
          <input type="text" name="type_name" placeholder="Enter type name" required />
        </div>
      </div>

      <div class="clup-row-single">
        <div class="clup-field">
          <label>Slug (Optional)</label>
          <input type="text" name="slug" placeholder="Enter slug (optional)" />
        </div>
      </div>

      <div class="clup-actions">
        <button type="submit" class="clup-btn clup-upload">Add Type</button>
        <button type="button" class="clup-btn clup-cancel">Cancel</button>
      </div>
    </form>
  </div>
</div>
