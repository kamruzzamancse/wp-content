<div class="cld-task-section">
    <div class="cld-task-header">
        <h2 class="header-title">Documents</h2>
        <button class="cld-upload-btn" data-modal="cl-upload-document-modal">
            Upload Document <span class="dashicons dashicons-media-document"></span>
        </button>
    </div>
    <!-- Dashboard Cards Grid -->
    <div class="stats-grid">
        <!-- Business Cards Card -->
        <a href="#" class="stat-card" data-type="business-cards">
            <h3>
                <span class="dashicons dashicons-admin-users"></span> 
                <?php echo esc_html__('Business Cards', 'text-domain'); ?>
            </h3>
        </a>
        <!-- Sellers Checklist Card -->
        <a href="#" class="stat-card" data-type="seller-checklist">
            <h3>
                <span class="dashicons dashicons-clipboard"></span> 
                <?php echo esc_html__('Sellers Checklist', 'text-domain'); ?>
            </h3>
        </a>
        <!-- Buyers Checklist Card -->
        <a href="#" class="stat-card" data-type="buyer-checklist">
            <h3>
                <span class="dashicons dashicons-portfolio"></span> 
                <?php echo esc_html__('Buyers Checklist', 'text-domain'); ?>
            </h3>
        </a>
    </div>
    <!-- Documents Table -->
    <div class="documents-section">
        <table class="documents-table">
            <thead>
                <tr>
                    <th style="width:50px; background:#2271b1; color:#fff;">#</th>
                    <th style="background:#2271b1; color:#fff;">Document Title</th>
                    <th style="background:#2271b1; color:#fff;">Document Type</th>
                    <th style="background:#2271b1; color:#fff;">File</th>
                    <th style="width:120px; background:#2271b1; color:#fff;">Actions</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
<?php 
    include locate_template('dashboard-templates/rt/rt-upload-document-modal.php');
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ===============================
    // Static dataset for each tab
    // ===============================
    const documentsData = {
        "business-cards": [
            { title: "Business Card 1", type: "Business Cards", file: "business1.pdf" },
            { title: "Business Card 2", type: "Business Cards", file: "business2.pdf" },
            { title: "Business Card 3", type: "Business Cards", file: "business3.pdf" },
            { title: "Business Card 4", type: "Business Cards", file: "business4.pdf" },
            { title: "Business Card 5", type: "Business Cards", file: "business5.pdf" }
        ],
        "seller-checklist": [
            { title: "Seller Checklist 1", type: "Seller Checklist", file: "seller1.pdf" },
            { title: "Seller Checklist 2", type: "Seller Checklist", file: "seller2.pdf" },
            { title: "Seller Checklist 3", type: "Seller Checklist", file: "seller3.pdf" },
            { title: "Seller Checklist 4", type: "Seller Checklist", file: "seller4.pdf" },
            { title: "Seller Checklist 5", type: "Seller Checklist", file: "seller5.pdf" }
        ],
        "buyer-checklist": [
            { title: "Buyer Checklist 1", type: "Buyer Checklist", file: "buyer1.pdf" },
            { title: "Buyer Checklist 2", type: "Buyer Checklist", file: "buyer2.pdf" },
            { title: "Buyer Checklist 3", type: "Buyer Checklist", file: "buyer3.pdf" },
            { title: "Buyer Checklist 4", type: "Buyer Checklist", file: "buyer4.pdf" },
            { title: "Buyer Checklist 5", type: "Buyer Checklist", file: "buyer5.pdf" }
        ]
    };
    // ===============================
    // Function to render table rows
    // ===============================
    function renderDocuments(type) {
        const tbody = document.querySelector('.documents-table tbody');
        tbody.innerHTML = ''; // Clear previous rows
        const data = documentsData[type];
        if(!data) return;
        data.forEach((doc, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td data-label="#">${index + 1}</td>
                <td data-label="Document Title">${doc.title}</td>
                <td data-label="Document Type">${doc.type}</td>
                <td data-label="File">${doc.file}</td>
                <td data-label="Actions">
                    <a href="#" class="doc-action download" title="Download">⬇️</a>
                    <a href="#" class="doc-action edit" title="Edit">✏️</a>
                    <a href="#" class="doc-action delete" title="Delete">🗑️</a>
                </td>
            `;
            tbody.appendChild(tr);
        });
        // Bind actions for dynamically created rows
        bindTableActions();
    }
    // ===============================
    // Bind actions for table buttons
    // ===============================
    function bindTableActions() {
        // Download
        document.querySelectorAll('.doc-action.download').forEach(link => {
            link.addEventListener('click', e => { 
                e.preventDefault(); 
                alert('Download document'); 
            });
        });
        // Edit -> Open same modal as upload button
        document.querySelectorAll('.doc-action.edit').forEach(link => {
            link.addEventListener('click', e => {
                e.preventDefault();
                const modal = document.getElementById('cl-upload-document-modal');
                if(modal) modal.classList.add('show');
            });
        });
        // Delete
        document.querySelectorAll('.doc-action.delete').forEach(link => {
            link.addEventListener('click', e => {
                e.preventDefault();
                alert('Delete document');
            });
        });
    }
    // ===============================
    // Tab click event
    // ===============================
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach(card => {
        card.addEventListener('click', e => {
            e.preventDefault();
            const type = card.dataset.type;
            // Render table for selected type
            renderDocuments(type);
            // Highlight active tab
            statCards.forEach(c => c.classList.remove('active'));
            card.classList.add('active');
        });
    });
    // ===============================
    // Initial load - default tab
    // ===============================
    renderDocuments('business-cards'); 
    document.querySelector('.stat-card[data-type="business-cards"]').classList.add('active');
    // ===============================
    // Modal functionality for Upload button
    // ===============================
    const modalButtons = document.querySelectorAll('.cld-upload-btn');
    modalButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = document.getElementById(btn.dataset.modal);
            if(modal) modal.classList.add('show');
        });
    });
    const closeButtons = document.querySelectorAll('.clup-close-btn, .clup-cancel');
    closeButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = btn.closest('.clup-modal-overlay');
            if(modal) modal.classList.remove('show');
        });
    });
    const modals = document.querySelectorAll('.clup-modal-overlay');
    modals.forEach(modal => {
        modal.addEventListener('click', e => {
            if(e.target === modal) modal.classList.remove('show');
        });
    });
    const browseButtons = document.querySelectorAll('.clup-browse');
    browseButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const fileInput = btn.parentElement.querySelector('.clup-file-input');
            if(fileInput) fileInput.click();
        });
    });
});
</script>

<style>
/* Container */
.cld-task-section {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    margin-bottom: 20px;
    width: 100%;
    box-sizing: border-box;
    overflow-x: hidden;
}

/* Header */
.cld-task-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.cld-upload-btn {
    background: #fff;
    border: 1px solid #0073e6;
    padding: 6px 14px;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
    color: #0073e6;
    display: flex;
    align-items: center;
    gap: 6px;
}

.cld-upload-btn:hover { 
    color: #FFF!important; 
    background: #0073e6; 
}

/* Dashboard Cards Grid */
.stats-grid {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.stat-card {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    width: 100%;
    max-width: 200px;
    padding: 20px;
    background: #f5f5f5;
    border-radius: 8px;
    text-decoration: none;
    color: #333;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    cursor: pointer;
}

.stat-card:hover { 
    background: #FFF; 
    box-shadow: 0 1px 3px rgba(0,0,0,0.05); 
}

.stat-card.active { 
    background:#2271b1; 
    color:#fff; 
}

.stat-card.active h3 { 
    color:#fff; 
}

.stat-card h3 {
    font-size: 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
}

/* Documents Table */
.documents-section { 
    margin-top: 30px; 
    overflow-x: auto; 
}

.documents-table {
    width: 100%;
    min-width: 600px;
    border-collapse: separate;
    border-spacing: 0;
    border: 1px solid #ddd;
    border-radius: 10px 10px 0 0;
    overflow: hidden;
}

/* Apply radius to first and last header cells */
.documents-table thead th:first-child {
    border-top-left-radius: 10px;
}

.documents-table thead th:last-child {
    border-top-right-radius: 10px;
}

.documents-table th, .documents-table td {
    padding: 12px 15px;
    text-align: left;
    font-size: 14px;
}

.documents-table th { 
    font-weight: 600; 
}

.doc-action {
    font-size: 14px;
    margin-right: 5px;
    text-decoration: none;
    cursor: pointer;
}

.doc-action.download { 
    color: #2f64e2; 
}

.doc-action.edit { 
    color: #ffb400; 
}

.doc-action.delete { 
    color: #e63946; 
}

.doc-action:hover { 
    text-decoration: underline; 
}

/* Tablet Responsive */
@media (max-width: 768px) {
    .stats-grid { 
        justify-content: center; 
        gap: 15px; 
    }
    
    /* Table Responsive Styles */
    .documents-section {
        overflow-x: visible;
    }
    
    .documents-table { 
        min-width: auto;
        border: none;
        border-radius: 0;
        background: transparent;
    }
    
    .documents-table thead { 
        display: none; 
    }
    
    .documents-table, 
    .documents-table tbody, 
    .documents-table tr, 
    .documents-table td { 
        display: block; 
        width: 100% !important;
    }
    
    .documents-table tbody {
        display: block;
        width: 100%;
    }
    
    .documents-table tr { 
        margin-bottom: 15px; 
        border: 1px solid #ddd; 
        border-radius: 8px; 
        padding: 10px; 
        background: #fff; 
        position: relative;
        box-sizing: border-box;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .documents-table td { 
        padding: 10px 10px 10px 45%; 
        border: none; 
        border-bottom: 1px solid #eee; 
        position: relative; 
        text-align: left; 
        min-height: 20px;
        box-sizing: border-box;
    }
    
    .documents-table td:last-child { 
        border-bottom: none; 
    }
    
    .documents-table td::before { 
        content: attr(data-label); 
        position: absolute; 
        left: 10px; 
        width: 40%; 
        padding-right: 10px; 
        white-space: nowrap; 
        text-align: left; 
        font-weight: 600; 
        color: #333; 
    }
    
    /* Special handling for Actions cell */
    .documents-table td[data-label="Actions"] {
        padding: 10px;
        text-align: center;
        border-bottom: none;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
    }
    
    .documents-table td[data-label="Actions"]::before {
        display: none;
    }
    
    .doc-action {
        margin: 0 5px;
        font-size: 16px;
    }
}

/* Mobile Responsive */
@media (max-width: 480px) {
    .cld-task-header { 
        flex-direction: column; 
        gap: 15px; 
        align-items: flex-start; 
    }
    
    .cld-upload-btn { 
        align-self: stretch; 
        justify-content: center; 
    }
    
    .stat-card { 
        max-width: 100%; 
        padding: 15px; 
    }
    
    .cld-task-section {
        padding: 10px;
    }
    
    /* Mobile Table Adjustments */
    .documents-table td {
        padding-left: 40%;
        font-size: 13px;
    }
    
    .documents-table td::before {
        width: 35%;
        font-size: 12px;
    }
    
    .doc-action {
        font-size: 16px;
        margin: 0 3px;
    }
}

</style>