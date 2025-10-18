<!-- Add Document Type Modal -->
<div id="cl-add-doc-type-modal" class="clup-modal-overlay" aria-hidden="true" role="dialog" aria-labelledby="cl-add-doc-type-title">
  <div class="clup-box" role="document">

    <!-- Close Button -->
    <button class="clup-close-btn" aria-label="Close modal">&times;</button>

    <!-- Modal Header -->
    <h1 id="cl-add-doc-type-title" class="clup-title">Add Document Type</h1>

    <!-- Form -->
    <form id="add-doc-type-form" class="clup-form" novalidate>
      
      <!-- Document Type Name -->
      <div class="clup-row-single">
        <div class="clup-field">
          <label for="doc-type-name">Document Type Name <span style="color:red;">*</span></label>
          <input id="doc-type-name" type="text" name="type_name" placeholder="Enter type name" required />
        </div>
      </div>

      <!-- Slug (Optional) -->
      <div class="clup-row-single">
        <div class="clup-field">
          <label for="doc-type-slug">Slug (Optional)</label>
          <input id="doc-type-slug" type="text" name="slug" placeholder="Enter slug (optional)" />
          <small>If left empty, a slug will be auto-generated from the type name.</small>
        </div>
      </div>

      <!-- Actions -->
      <div class="clup-actions">
        <button type="submit" class="clup-btn clup-upload">Add Type</button>
        <button type="button" class="clup-btn clup-cancel">Cancel</button>
      </div>

    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('cl-add-doc-type-modal');
    const form = document.getElementById('add-doc-type-form');
    const addBtn = document.getElementById('addDocTypeBtn');
    const closeBtns = modal.querySelectorAll('.clup-close-btn, .clup-cancel');

    // Open modal
    addBtn.addEventListener('click', () => {
        modal.classList.add('show');
        modal.setAttribute('aria-hidden', 'false');
        form.reset();
    });

    // Close modal
    closeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
        });
    });

    // Close modal when clicking outside modal box
    modal.addEventListener('click', e => {
        if (e.target === modal) {
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
        }
    });

    // Handle form submission (AJAX)
    form.addEventListener('submit', e => {
        e.preventDefault();
        const typeName = form.type_name.value.trim();
        let slug = form.slug.value.trim();

        if (!typeName) return alert('Please enter a document type name.');
        slug = slug || ''; // Let PHP auto-generate if empty

        // AJAX request
        jQuery.post(rt_doc_type_ajax.ajax_url, {
            action: 'rt_add_document_type',
            nonce: rt_doc_type_ajax.nonce,
            type_name: typeName,
            slug: slug
        }, function(res) {
            if (res.success) {
                const tbody = document.querySelector('.doc-types-table tbody');

                // Prepend new row
                const tr = document.createElement('tr');
                tr.dataset.id = res.data.id;
                tr.innerHTML = `
                    <td>1</td>
                    <td>${res.data.type_name}</td>
                    <td>${res.data.slug}</td>
                    <td>
                        <span class="edit-doc-type" title="Edit">✏️</span>
                        <span class="delete-doc-type" title="Delete">🗑️</span>
                    </td>
                `;
                tbody.prepend(tr);

                // Re-number all rows
                tbody.querySelectorAll('tr').forEach((row, i) => {
                    row.querySelector('td').textContent = i + 1;
                });

                form.reset();
                modal.classList.remove('show');
                modal.setAttribute('aria-hidden', 'true');
            } else {
                alert(res.data || 'Failed to add document type.');
            }
        });
    });
});
</script>
