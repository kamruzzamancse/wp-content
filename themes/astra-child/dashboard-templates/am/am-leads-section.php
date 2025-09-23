<div class="dashboard-section leads-section">
    <div class="leads-header">
        <h1 class="header-title">Leads</h1>
        <button id="addLeadBtn" class="add-lead-btn">+</button>
    </div>
    <table class="leads-table">
        <thead>
            <tr>
                <th>Client Name</th>
                <th>Last Touch</th>
                <th>Status</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $leads = [
                ['name' => 'John Smith', 'time' => '20 July 25, 11pm', 'status' => 'hot', 'notes' => 'Contract update'],
                ['name' => 'Sarah Johnson', 'time' => '19 July 25, 3pm', 'status' => 'warm', 'notes' => 'Follow up needed']
            ];
            
            foreach ($leads as $lead): ?>
            <tr>
                <td data-label="Client Name"><?php echo esc_html($lead['name']); ?></td>
                <td data-label="Last Touch"><?php echo esc_html($lead['time']); ?></td>
                <td data-label="Status">
                    <span class="status-dot status-<?php echo esc_attr($lead['status']); ?>"></span>
                    <?php echo ucfirst($lead['status']); ?>
                </td>
                <td data-label="Notes"><?php echo esc_html($lead['notes']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="lead-popup-overlay" id="leadPopupOverlay">
    <div class="lead-popup">
        <button class="close-popup">&times;</button>
        <div class="popup-header">
            <h1 class="popup-heading">Leads</h1><br />
            <h2 class="popup-client-name">John Smith</h2>
        </div>
        
        <div class="popup-grid">
            <div class="popup-column">
                <div class="popup-section">
                    <span class="popup-label">Last Touch</span>
                    <span class="popup-value">20 July 25, 11pm</span>
                </div>
            </div>
            
            <div class="popup-column">
                <div class="popup-section">
                    <span class="popup-label">Status</span>
                    <div class="status-container">
                        <span class="status-dot status-hot"></span>
                        <span class="status-text">Hot</span>
                    </div>
                </div>
            </div>
            
            <div class="popup-fullwidth">
                <div class="popup-section">
                    <span class="popup-label">Notes</span>
                    <p class="popup-value">Just a quick update about contract.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>

/* css for lead add modal */
.add-lead-title, #clientSelect, #statusSelect, #notesInput {
  margin-bottom: 20px
}
/* header */
.lead-add-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 12px;
}
.close-lead-modal {
  font-size: 20px;
  cursor: pointer;
}

/* footer */
.lead-add-footer {
  display: flex;
  justify-content: flex-end; /* aligns button to right */
  margin-top: 16px;
}

#saveLeadBtn {
  background: #2980b9;
  color: white;
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}
#saveLeadBtn:hover {
  background: #096cad;
}
.lead-add-modal {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.6);
  justify-content: center;
  align-items: center;
  z-index: 2000;
}
.lead-add-content {
  background: #fff;
  padding: 20px;
  border-radius: 10px;
  width: 400px;
  max-width: 90%;
}
#notesInput {
  width: 100%;
  padding: 8px;
  border-radius: 6px;
  border: 1px solid #ccc;
  resize: vertical; /* allow vertical resizing only */
  font-family: inherit;
  font-size: 14px;
}
#notesInput:focus {
  outline: none;
  border-color: #2980b9;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}
.
</style>

<style>
/* General Styling */
.dashboard-section {
  padding: 20px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  overflow-x: auto;
}

/* Table Styling (Desktop) */
.leads-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.leads-table th,
.leads-table td {
  padding: 10px;
  text-align: left;
  border-bottom: 1px solid #eee;
}

/* .leads-table th {
  background: #f5f5f5;
  font-weight: 600;
  color: #444;
} */

/* Status Dots */
.status-dot {
  display: inline-block;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  margin-right: 6px;
}

.status-hot { background-color: #e74c3c; }   /* Red */
.status-warm { background-color: #f39c12; }  /* Orange */
.status-cold { background-color: #3498db; }  /* Blue */

.add-lead-btn {
    padding: 8px 16px;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    font-weight: 500;
    font-size: 14px;
    white-space: nowrap;
    min-width: 100px;
    color: #FFF!important;
}
.add-lead-btn:hover {
  background: #2980b9;
}
.leads-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.popup-client-name {
  font-size: 20px!important;
}

/* Mobile Responsive (Card Style) */
@media screen and (max-width: 768px) {
  .leads-table,
  .leads-table thead,
  .leads-table tbody,
  .leads-table th,
  .leads-table tr {
    display: block;
    width: 100%;
  }

  .leads-table thead {
    display: none;
  }

  .leads-table tr {
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 12px;
    background: #f9f9ff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  }

  .leads-table td {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
  }

  .leads-table td:last-child {
    border-bottom: none;
  }

  .leads-table td::before {
    content: attr(data-label);
    font-weight: 600;
    color: #333;
    flex: 1; /* takes left side */
    text-align: left;
  }

  /* FIX: Keep status dot + text together */
  .leads-table td[data-label="Status"] {
    justify-content: flex-start;
    gap: 10px;
  }

  .leads-table td[data-label="Status"]::before {
    margin-right: auto; /* keeps "Status" label aligned left */
  }

  .leads-table td[data-label="Status"] span,
  .leads-table td[data-label="Status"] {
    align-items: center;
    gap: 6px;
  }

}

</style>

<script>
  /* js for modal control new row insert*/
  const modal = document.getElementById('leadAddModal');
  const openBtn = document.getElementById('addLeadBtn');
  const closeBtn = document.querySelector('.close-lead-modal');
  const saveBtn = document.getElementById('saveLeadBtn');
  const leadsTable = document.querySelector('.leads-table tbody');

  openBtn.addEventListener('click', () => modal.style.display = 'flex');
  closeBtn.addEventListener('click', () => modal.style.display = 'none');
  window.addEventListener('click', e => { if(e.target === modal) modal.style.display = 'none'; });

  saveBtn.addEventListener('click', () => {
    const client = document.getElementById('clientSelect').value;
    const status = document.getElementById('statusSelect').value;
    const notes = document.getElementById('notesInput').value;

    if (!client) return alert("Please select a client!");

     // Custom date formatting
    const now = new Date();
    const day = now.getDate();
    const month = now.toLocaleString('default', { month: 'long' }); // e.g., "July"
    const year = now.getFullYear().toString().slice(-2); // last 2 digits of year
    let hours = now.getHours();
    const ampm = hours >= 12 ? 'pm' : 'am';
    hours = hours % 12 || 12; // convert to 12-hour format

    const formattedDate = `${day} ${month} ${year}, ${hours}${ampm}`;

    const row = document.createElement('tr');
    row.innerHTML = `
      <td data-label="Client Name">${client}</td>
      <td data-label="Last Touch">${formattedDate}</td>
      <td data-label="Status">
          <span class="status-dot status-${status}"></span>
          ${status.charAt(0).toUpperCase() + status.slice(1)}
      </td>
      <td data-label="Notes">${notes}</td>
    `;

    leadsTable.appendChild(row);
    modal.style.display = 'none';
  });
</script>