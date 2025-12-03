<!-- Document Types Section -->
<div class="cld-task-section">

    <div class="cld-doc-types-wrapper">
        <div class="cld-doc-types-header">
            <h2 class="header-title">Document Types</h2>
            <button id="addDocTypeBtn" class="btn-primary">+ Add Type</button>
        </div>

        <!-- Controls: Show dropdown (right side, label left) -->
        <div class="doc-types-controls" style="display:flex; justify-content:flex-end; align-items:center; margin-bottom:10px; gap:5px;">
            <label for="docTypeRowsPerPage">Show:</label>
            <select id="docTypeRowsPerPage">
                <option value="5" selected>5 rows</option>
                <option value="10">10 rows</option>
                <option value="25">25 rows</option>
            </select>
        </div>

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
                global $wpdb;
                $doc_types = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}document_types WHERE deleted_at IS NULL ORDER BY created_at DESC");
                
                if ($doc_types):
                    $serial = 1; // Use separate serial counter
                    foreach ($doc_types as $type):
                        if (!$type) continue; // skip null/empty objects
                ?>
                    <tr data-id="<?php echo esc_attr($type->id); ?>">
                        <td><?php echo $serial++; ?></td>
                        <td><?php echo esc_html($type->type_name); ?></td>
                        <td>
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

        <!-- Pagination (table bottom, right aligned) -->
        <div id="docTypesPagination" class="pagination" style="display:flex; justify-content:flex-end; margin-top:10px;"></div>
    </div>

</div>

<!-- ================= Documents Section ================= -->
<div class="cld-task-section">

    <h2 class="header-title">Documents</h2>

    <!-- Controls: Search + Show -->
    <div class="cld-doc-top-flex" style="margin-bottom:15px; justify-content: space-between; align-items: center; flex-wrap: wrap;">

        <!-- Tabs -->
        <div class="cld-doc-type-tabs-wrapper">
            <button class="doc-type-tab active" data-type="all">All</button>
            <?php foreach ($doc_types as $type): ?>
                <button class="doc-type-tab" data-type="<?php echo esc_attr($type->id); ?>">
                    <?php echo esc_html($type->type_name); ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Search + Show dropdown -->
        <div style="display:flex; align-items:center; gap:10px; position: relative;">
            <div class="cld-doc-search-wrapper">
                <input type="text" id="documentSearchInput" placeholder="Search documents..." />
                <ul id="documentSearchSuggestions" class="auto-suggest-list"></ul>
            </div>
            <div class="cld-dropdown" style="display:flex; align-items:center; gap:5px;">
                <span>Show:</span>
                <select id="rowsPerPage">
                    <option value="5" selected>5 rows</option>
                    <option value="10">10 rows</option>
                    <option value="25">25 rows</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Documents Table -->
    <div class="documents-section">
        <table class="documents-table">
            <thead>
                <tr>
                    <th style="width:50px;">#</th>
                    <th>Document Title</th>
                    <th>Document Type</th>
                    <th>Document</th>
                    <th>Assigned/Replied</th>
                    <th style="width:80px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $table_docs     = $wpdb->prefix . 'documents';
                $table_types    = $wpdb->prefix . 'document_types';
                $assigned_table = $wpdb->prefix . 'assigned_tasks';

                $documents = $wpdb->get_results("
                    SELECT d.id, d.title, d.file_name, d.type_id, d.doc_type, dt.type_name
                    FROM $table_docs d
                    LEFT JOIN $table_types dt ON d.type_id = dt.id
                    WHERE d.deleted_at IS NULL
                    ORDER BY d.created_at DESC
                ");

                if ($documents):
                    foreach ($documents as $index => $doc):
                        $upload_dir = wp_upload_dir();
                        $file_url  = $doc->file_name;
                        $file_name = basename($doc->file_name);
                        $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $file_url);

                        $assigned_status = $doc->doc_type ?: 'Assigned';
                        $assignment = $wpdb->get_row($wpdb->prepare("
                            SELECT created_at, updated_at 
                            FROM $assigned_table
                            WHERE document_id=%d AND deleted_at IS NULL
                            ORDER BY id DESC
                            LIMIT 1
                        ", $doc->id));

                        if ($assignment) {
                            $assigned_date = $assignment->created_at;
                            $reply_date    = $assignment->updated_at;
                            $assigned_status = ($reply_date && $reply_date !== $assigned_date) ?
                                "Replied on " . date('d M Y', strtotime($reply_date)) :
                                "Assigned on " . date('d M Y', strtotime($assigned_date));
                        }
                        ?>
                        <tr data-id="<?php echo esc_attr($doc->id); ?>" data-type-id="<?php echo esc_attr($doc->type_id); ?>">
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo esc_html($doc->title); ?></td>
                            <td><?php echo esc_html($doc->type_name); ?></td>
                            <td>
                                <?php if (file_exists($file_path)) : ?>
                                    <a href="<?php echo esc_url($file_url); ?>" target="_blank">
                                        <?php echo esc_html($file_name); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="missing-file">File missing</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html($assigned_status); ?></td>
                            <td>
                                <?php if (file_exists($file_path)): ?>
                                    <a href="<?php echo esc_url($file_url); ?>" download="<?php echo esc_attr($file_name); ?>" class="doc-action download" title="Download">⬇️</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach;
                else: ?>
                    <tr><td colspan="6" style="text-align:center;">No Documents Found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <div id="documentsPagination" class="pagination" style="margin-top:10px; display:flex; justify-content:flex-end; gap:5px;"></div>
    </div>

</div>

<?php 
include locate_template('dashboard-templates/rt/rt-upload-document-modal.php');
include locate_template('dashboard-templates/rt/rt-document-type-modal.php');
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = document.querySelector('.doc-types-table tbody');
    const rows = Array.from(table.querySelectorAll('tr'));
    const rowsPerPageSelect = document.getElementById('docTypeRowsPerPage');
    const paginationContainer = document.getElementById('docTypesPagination');

    let currentPage = 1;
    let rowsPerPage = parseInt(rowsPerPageSelect.value);

    function renderTable() {
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;

        rows.forEach((row, index) => {
            row.style.display = (index >= start && index < end) ? '' : 'none';
        });
    }

    function renderPagination() {
        paginationContainer.innerHTML = '';
        const pageCount = Math.ceil(rows.length / rowsPerPage);

        for(let i = 1; i <= pageCount; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            btn.classList.add('page-btn'); // same class as documents

            if (i === currentPage) {
                btn.classList.add('active'); // highlight active page
            }

            btn.addEventListener('click', () => {
                currentPage = i;
                renderTable();
                renderPagination();
            });

            paginationContainer.appendChild(btn);
        }
    }

    rowsPerPageSelect.addEventListener('change', function() {
        rowsPerPage = parseInt(this.value);
        currentPage = 1;
        renderTable();
        renderPagination();
    });

    renderTable();
    renderPagination();
});
</script>

<!-- JS: Tabs, Search, Pagination -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Tabs Filtering ---
    const tabs = document.querySelectorAll('.doc-type-tab');
    const rows = document.querySelectorAll('.documents-table tbody tr');

    function filterByType(typeId) {
        rows.forEach(row => {
            row.style.display = (typeId === 'all' || row.dataset.typeId === typeId) ? '' : 'none';
        });
        currentPage = 1;
        renderDocumentsTable();
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            filterByType(tab.dataset.type);
            document.getElementById('documentSearchInput').value = '';
            document.getElementById('documentSearchSuggestions').innerHTML = '';
        });
    });

    // --- Search Auto-Suggestion ---
    const searchInput = document.getElementById('documentSearchInput');
    const suggestions = document.getElementById('documentSearchSuggestions');

    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        suggestions.innerHTML = '';

        const activeTab = document.querySelector('.doc-type-tab.active');
        const activeTypeId = activeTab.dataset.type;

        const matched = [];
        rows.forEach(row => {
            const title = row.children[1].textContent.toLowerCase();
            const typeId = row.dataset.typeId;
            if(title.includes(query) && (activeTypeId === 'all' || activeTypeId === typeId)){
                row.style.display = '';
                matched.push(title);
            } else {
                row.style.display = 'none';
            }
        });

        [...new Set(matched)].slice(0, 5).forEach(s => {
            const li = document.createElement('li');
            li.textContent = s;
            li.addEventListener('click', () => {
                searchInput.value = s;
                suggestions.innerHTML = '';
                rows.forEach(row => {
                    row.style.display = (row.children[1].textContent.toLowerCase() === s.toLowerCase()) ? '' : 'none';
                });
            });
            suggestions.appendChild(li);
        });
    });

    document.addEventListener('click', e => {
        if(!searchInput.contains(e.target)){
            suggestions.innerHTML = '';
        }
    });

    // --- Pagination ---
    const rowsPerPageSelect = document.getElementById('rowsPerPage');
    const paginationContainer = document.getElementById('documentsPagination');
    const docRows = Array.from(document.querySelectorAll('.documents-table tbody tr'));
    let currentPage = 1;
    let rowsPerPage = parseInt(rowsPerPageSelect.value);

    function renderDocumentsTable() {
        const activeTab = document.querySelector('.doc-type-tab.active');
        const activeTypeId = activeTab.dataset.type;

        const filteredRows = docRows.filter(row => activeTypeId === 'all' || row.dataset.typeId === activeTypeId);

        const totalRows = filteredRows.length;
        const totalPages = Math.ceil(totalRows / rowsPerPage);

        filteredRows.forEach((row, index) => {
            const start = (currentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            row.style.display = (index >= start && index < end) ? '' : 'none';
        });

        renderPagination(totalPages, filteredRows);
    }

    function renderPagination(totalPages, filteredRows) {
        paginationContainer.innerHTML = '';
        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            btn.classList.add('page-btn');
            if (i === currentPage) btn.classList.add('active');
            btn.addEventListener('click', () => {
                currentPage = i;
                renderDocumentsTable();
            });
            paginationContainer.appendChild(btn);
        }
    }

    rowsPerPageSelect.addEventListener('change', () => {
        rowsPerPage = parseInt(rowsPerPageSelect.value);
        currentPage = 1;
        renderDocumentsTable();
    });

    renderDocumentsTable();
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // --- Upload Modal ---
    const uploadButtons = document.querySelectorAll('.cld-upload-btn');
    uploadButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const modalId = btn.dataset.modal;
            const modal = document.getElementById(modalId);
            if (modal) modal.classList.add('show');
            const form = modal.querySelector('form');
            if (form) form.reset();
            const hiddenInput = form.querySelector('[name="document_id"]');
            if (hiddenInput) hiddenInput.value = '';
        });
    });

    const closeButtons = document.querySelectorAll('.clup-close-btn');
    closeButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            btn.closest('.clup-modal-overlay').classList.remove('show');
        });
    });

    const browseButtons = document.querySelectorAll('.clup-browse');
    browseButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const fileInput = btn.parentElement.querySelector('.clup-file-input');
            if(fileInput) fileInput.click();
        });
    });

    // --- Tabs Filtering ---
    const tabs = document.querySelectorAll('.doc-type-tab');
    const rows = document.querySelectorAll('.documents-table tbody tr');

    function filterByType(typeId) {
        rows.forEach(row => {
            row.style.display = (typeId === 'all' || row.dataset.typeId === typeId) ? '' : 'none';
        });
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            filterByType(tab.dataset.type);
            document.getElementById('documentSearchInput').value = '';
            document.getElementById('documentSearchSuggestions').innerHTML = '';
        });
    });

    // --- Search Auto-Suggestion ---
    const searchInput = document.getElementById('documentSearchInput');
    const suggestions = document.getElementById('documentSearchSuggestions');

    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        suggestions.innerHTML = '';

        const activeTab = document.querySelector('.doc-type-tab.active');
        const activeTypeId = activeTab.dataset.type;

        const matched = [];
        rows.forEach(row => {
            const title = row.children[1].textContent.toLowerCase();
            const typeId = row.dataset.typeId;
            if(title.includes(query) && (activeTypeId === 'all' || activeTypeId === typeId)){
                row.style.display = '';
                matched.push(title);
            } else {
                row.style.display = 'none';
            }
        });

        [...new Set(matched)].slice(0, 5).forEach(s => {
            const li = document.createElement('li');
            li.textContent = s;
            li.addEventListener('click', () => {
                searchInput.value = s;
                suggestions.innerHTML = '';
                rows.forEach(row => {
                    row.style.display = (row.children[1].textContent.toLowerCase() === s.toLowerCase()) ? '' : 'none';
                });
            });
            suggestions.appendChild(li);
        });
    });

    document.addEventListener('click', e => {
        if(!searchInput.contains(e.target)){
            suggestions.innerHTML = '';
        }
    });
});
</script>

<style>
/* ==========================
   General Container Styles
========================== */
.cld-task-section, .cld-doc-types-section {
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
   Top Flex Layout (Left + Right)
========================== */
.cld-top-flex {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
}

.cld-doc-types-wrapper {
    flex: 1;
    min-width: 280px;
}

.cld-doc-search-wrapper {
    flex: 1;
    min-width: 280px;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

/* ==========================
   Headers
========================== */
.cld-task-header, .cld-doc-types-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

/* ==========================
   Upload / Add Button
========================== */
.cld-upload-btn, .btn-primary {
    background: #0073e6;
    color: #fff !important;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
}

.doc-type-tab {
    color: #FFF!important;
}

.cld-upload-btn:hover, .btn-primary:hover {
    background: #005bb5;
}

/* ==========================
   Dashboard Cards
========================== */
.stats-grid {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.stat-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    max-width: 200px;
    padding: 20px;
    background: #f5f5f5;
    border-radius: 8px;
    color: #333;
    text-decoration: none;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    cursor: pointer;
    transition: all 0.3s ease;
}

.stat-card:hover {
    background: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.stat-card.active {
    background: #2271b1;
    color: #fff;
}

.stat-card.active h3 { color: #fff; }

.stat-card h3 {
    font-size: 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
}

/* ==========================
   Tables Common
========================== */
.documents-table, .doc-types-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    border: 1px solid #ddd;
    border-radius: 10px 10px 0 0;
    overflow: hidden;
}

.documents-table th, .documents-table td,
.doc-types-table th, .doc-types-table td {
    padding: 12px 15px;
    font-size: 14px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.documents-table th, .doc-types-table th {
    background: #2271b1;
    color: #fff;
    font-weight: 600;
}

.documents-table th:first-child, .doc-types-table th:first-child {
    border-top-left-radius: 10px;
}
.documents-table th:last-child, .doc-types-table th:last-child {
    border-top-right-radius: 10px;
}

.documents-table td:last-child, .doc-types-table td:last-child {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    text-align: center;
    min-height: 45px;
}

.edit-doc-type:hover { color: #ffb400; cursor: pointer; }
.delete-doc-type:hover { color: #e63946; cursor: pointer; }

/* ==========================
   Documents Table Specific
========================== */
.documents-section { margin-top: 30px; overflow-x: auto; }

.doc-action { font-size: 14px; margin-right: 5px; cursor: pointer; }
.doc-action.download { color: #2f64e2; }
.doc-action.edit { color: #ffb400; }
.doc-action.delete { color: #e63946; }
.doc-action:hover { text-decoration: underline; }

.missing-file { color: red; font-weight: 500; }

/* ==========================
   Auto-Suggestion List
========================== */
.auto-suggest-list {
    list-style: none;
    padding: 0;
    margin: 0;
    border: 1px solid #ddd;
    border-radius: 6px;
    max-height: 200px;
    overflow-y: auto;
    background: #fff;
    position: absolute;
    width: 100%;
    z-index: 1000;
}

.auto-suggest-list li {
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
}

.auto-suggest-list li:last-child { border-bottom: none; }
.auto-suggest-list li:hover { background: #f0f0f0; }

/* ==========================
   Tablet Responsive (≤1024px)
========================== */
@media (max-width: 1024px) {
    /* Stack top controls */
    .cld-doc-top-flex {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }

    .cld-doc-search-wrapper {
        width: 100%;
        max-width: 100%;
        margin-top: 10px;
        margin-bottom: 15px;
        align-items: flex-start;
    }

    .cld-doc-type-tabs-wrapper {
        flex-wrap: wrap;
        gap: 8px;
    }
}

/* ==========================
   Mobile / Tablet Responsive
========================== */
@media (max-width: 768px) {
    /* Stack top controls */
    .cld-doc-top-flex {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 10px;
    }

    /* Search + show wrapper */
    .cld-doc-top-flex > div:last-child {
        flex-basis: 100% !important;  /* take full width */
        display: flex !important;
        flex-direction: column;       /* stack input and dropdown */
        align-items: flex-start;
        gap: 8px;
        margin-top: 8px;
    }

    /* Search input full width */
    .cld-doc-search-wrapper input {
        width: 100% !important;
        max-width: 100%;
    }

    /* Show dropdown full width */
    .cld-doc-top-flex > div:last-child select {
        width: 100% !important;
        max-width: 200px;
    }

    /* Tables responsive */
    .documents-table, .doc-types-table {
        width: 100%;
        border-collapse: collapse;
    }

    .documents-table thead, .doc-types-table thead { display: none; }
    .documents-table tbody, .doc-types-table tbody { display: block; }

    .documents-table tr, .doc-types-table tr {
        display: block;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 10px;
        background: #fff;
        position: relative;
    }

    .documents-table td, .doc-types-table td {
        display: block;
        padding-left: 40%;
        position: relative;
        font-size: 13px;
        border: none;
        border-bottom: 1px solid #eee;
    }

    .documents-table td:last-child, 
    .doc-types-table td:last-child {
        display: flex;
        justify-content: left;
        border-bottom: none; 
    }

    .cld-dropdown {
        display:flex; 
        align-items:left!important; 
        gap:5px;
    }

    .documents-table td::before, .doc-types-table td::before {
        content: attr(data-label);
        position: absolute;
        left: 10px;
        width: 35%;
        white-space: nowrap;
        font-weight: 600;
        font-size: 12px;
    }

    .documents-table td[data-label="Actions"], .doc-types-table td[data-label="Actions"] {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        padding: 10px 0;
    }
    .documents-table td[data-label="Actions"]::before,
    .doc-types-table td[data-label="Actions"]::before { display: none; }

    /* Optional: stat cards and task section */
    .stat-card { max-width: 100%; padding: 15px; }
    .cld-task-section { padding: 10px; }
}


/* Container for Tabs + Search (flex row) */
.cld-doc-top-flex {
    display: flex;
    justify-content: space-between; /* tabs left, search right */
    align-items: center;           /* vertical align */
    gap: 20px;
    flex-wrap: wrap;               /* responsive wrap */
    margin-bottom: 20px;
}

/* Tabs container */
.cld-doc-type-tabs-wrapper {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

/* Search wrapper */
.cld-doc-search-wrapper {
    flex-shrink: 0;
    position: relative;
}

/* Input field styling */
.cld-doc-search-wrapper input {
    padding: 8px 12px;
    border-radius: 6px;
    border: 1px solid #ddd;
    width: 100%;
    max-width: 250px;
}

/* Auto-suggest list */
.auto-suggest-list {
    width: 300px;
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #ddd;
    border-radius: 6px;
    background: #fff;
    position: absolute;
    top: 38px; /* below input */
    right: 0;
    z-index: 1000;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

/* Pagination buttons */
.page-btn {
    padding: 5px 10px;
    border: 1px solid #ddd;
    background: #fff;
    cursor: pointer;
    border-radius: 4px;
    margin-right: 6px;
}

.page-btn.active {
    background: #2271b1;
    color: #fff!important;
    border-color: #2271b1;
}

#rowsPerPage {
    padding: 5px 10px;
    border-radius: 6px;
    border: 1px solid #ddd;
    cursor: pointer;
}

#docTypeRowsPerPage {
    padding: 5px 10px;
    border-radius: 6px;
    border: 1px solid #ddd;
    cursor: pointer;
    width: 85px; /* Fixed width */
    background: #fff;
    font-size: 14px;
}

.doc-types-controls label {
    font-size: 14px;
    margin-right: 6px;
    font-weight: 500;
}

</style>
