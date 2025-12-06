<!-- Add Document Type Modal -->
<div id="cl-add-doc-type-modal" class="clup-modal-overlay" aria-hidden="true" role="dialog" aria-labelledby="cl-add-doc-type-title">
  <div class="clup-box" role="document">

    <!-- Close Button -->
    <button class="clup-close-btn" aria-label="Close modal">&times;</button>

    <!-- Modal Header -->
    <h1 id="cl-add-doc-type-title" class="clup-title" style="font-size:1.75rem">Add Document Type</h1> <br>

    <!-- Form -->
    <form id="add-doc-type-form" class="clup-form" novalidate>
      
      <!-- Document Type Name -->
      <div class="clup-row-single">
        <div class="clup-field">
          <label for="doc-type-name">Document Type Name <span style="color:red;">*</span></label>
          <input id="doc-type-name" type="text" name="type_name" placeholder="Enter type name" required />
        </div>
      </div>

      <!-- Actions -->
      <div class="clup-actions">
        <button type="button" class="clup-btn clup-cancel">Cancel</button>
        <button type="submit" class="clup-btn clup-upload">Add Type</button>
      </div>

    </form>
  </div>
</div>
