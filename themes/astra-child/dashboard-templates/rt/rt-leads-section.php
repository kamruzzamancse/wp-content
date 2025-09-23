<div class="dashboard-section leads-section">
    <div class="leads-header">
        <h1 class="header-title">Leads</h1>
        <button id="addLeadBtn" class="add-lead-btn">+ Add Lead</button>
    </div>
    <table class="leads-table">
        <thead>
            <tr>
                <th>Client Name</th>
                <th>Last Touch</th>
                <th>Status</th>
                <th>Notes</th>
                <th style="width:140px">Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Preloaded demo data -->
            <tr>
                <td data-label="Client Name">John Smith</td>
                <td data-label="Last Touch">20 July 25, 11pm</td>
                <td data-label="Status"><span class="status-dot status-hot"></span>Hot</td>
                <td data-label="Notes">Contract update</td>
                <td data-label="Actions" class="action-cell">
                    <span class="edit-lead-btn" title="Edit">✏️</span>
                    <span class="convert-lead-btn" title="Convert to Client">🔄</span>
                    <span class="delete-lead-btn" title="Delete">🗑️</span>
                </td>
            </tr>
            <tr>
                <td data-label="Client Name">Sarah Johnson</td>
                <td data-label="Last Touch">19 July 25, 3pm</td>
                <td data-label="Status"><span class="status-dot status-warm"></span>Warm</td>
                <td data-label="Notes">Follow up needed</td>
                <td data-label="Actions" class="action-cell">
                    <span class="edit-lead-btn" title="Edit">✏️</span>
                    <span class="convert-lead-btn" title="Convert to Client">🔄</span>
                    <span class="delete-lead-btn" title="Delete">🗑️</span>
                </td>
            </tr>
            <tr>
                <td data-label="Client Name">Michael Brown</td>
                <td data-label="Last Touch">15 July 25, 10am</td>
                <td data-label="Status"><span class="status-dot status-cold"></span>Cold</td>
                <td data-label="Notes">No response</td>
                <td data-label="Actions" class="action-cell">
                    <span class="edit-lead-btn" title="Edit">✏️</span>
                    <span class="convert-lead-btn" title="Convert to Client">🔄</span>
                    <span class="delete-lead-btn" title="Delete">🗑️</span>
                </td>
            </tr>
            <tr>
                <td data-label="Client Name">Emily Carter</td>
                <td data-label="Last Touch">12 July 25, 5pm</td>
                <td data-label="Status"><span class="status-dot status-warm"></span>Warm</td>
                <td data-label="Notes">Requested brochure</td>
                <td data-label="Actions" class="action-cell">
                    <span class="edit-lead-btn" title="Edit">✏️</span>
                    <span class="convert-lead-btn" title="Convert to Client">🔄</span>
                    <span class="delete-lead-btn" title="Delete">🗑️</span>
                </td>
            </tr>
            <tr>
                <td data-label="Client Name">David Wilson</td>
                <td data-label="Last Touch">10 July 25, 2pm</td>
                <td data-label="Status"><span class="status-dot status-hot"></span>Hot</td>
                <td data-label="Notes">Ready to discuss contract</td>
                <td data-label="Actions" class="action-cell">
                    <span class="edit-lead-btn" title="Edit">✏️</span>
                    <span class="convert-lead-btn" title="Convert to Client">🔄</span>
                    <span class="delete-lead-btn" title="Delete">🗑️</span>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Lead Add/Edit Modal -->
<div class="lead-add-modal" id="leadAddModal">
  <div class="lead-add-content">

    <div class="lead-add-header">
      <h1 class="header-title">Add / Edit Lead</h1>
      <span class="close-lead-modal">&times;</span>
    </div>

    <label for="clientSelect">Client Name:</label>
    <input type="text" id="clientSelect" placeholder="Enter client name"><br />

    <label for="statusSelect">Status:</label>
    <select id="statusSelect">
        <option value="hot">Hot</option>
        <option value="warm">Warm</option>
        <option value="cold">Cold</option>
    </select><br />

    <label for="notesInput">Notes:</label>
    <textarea id="notesInput" placeholder="Write notes..." rows="4"></textarea><br />

    <div class="lead-add-footer">
      <button id="saveLeadBtn">Save Lead</button>
    </div>

  </div>
</div>

<style>
/* extra actions styling */
.action-cell span {
  cursor: pointer;
  margin: 0 4px;
  font-size: 18px;
  transition: transform 0.2s;
}
.action-cell span:hover {
  transform: scale(1.2);
}
</style>

<script>
const modal = document.getElementById('leadAddModal');
const openBtn = document.getElementById('addLeadBtn');
const closeBtn = document.querySelector('.close-lead-modal');
const saveBtn = document.getElementById('saveLeadBtn');
const leadsTable = document.querySelector('.leads-table tbody');
let editingRow = null;

// open modal
openBtn.addEventListener('click', () => {
  editingRow = null;
  document.getElementById('clientSelect').value = "";
  document.getElementById('statusSelect').value = "hot";
  document.getElementById('notesInput').value = "";
  modal.style.display = 'flex';
});

// close modal
closeBtn.addEventListener('click', () => modal.style.display = 'none');
window.addEventListener('click', e => { if(e.target === modal) modal.style.display = 'none'; });

// action handlers
document.addEventListener('click', e => {
  // edit
  if (e.target.closest('.edit-lead-btn')) {
    editingRow = e.target.closest('tr');
    document.getElementById('clientSelect').value = editingRow.querySelector('td[data-label="Client Name"]').innerText;
    document.getElementById('statusSelect').value = editingRow.querySelector('td[data-label="Status"]').innerText.trim().toLowerCase();
    document.getElementById('notesInput').value = editingRow.querySelector('td[data-label="Notes"]').innerText;
    modal.style.display = 'flex';
  }

  // delete
  if (e.target.closest('.delete-lead-btn')) {
    if (confirm("Are you sure you want to delete this lead?")) {
      e.target.closest('tr').remove();
    }
  }

  // convert
  if (e.target.closest('.convert-lead-btn')) {
    alert("Lead converted to Client (frontend demo only).");
    e.target.closest('tr').remove();
  }
});

// save new or edited
saveBtn.addEventListener('click', () => {
  const client = document.getElementById('clientSelect').value;
  const status = document.getElementById('statusSelect').value;
  const notes = document.getElementById('notesInput').value;
  if (!client) return alert("Please enter a client name!");

  if (editingRow) {
    editingRow.querySelector('td[data-label="Client Name"]').innerText = client;
    editingRow.querySelector('td[data-label="Status"]').innerHTML = `<span class="status-dot status-${status}"></span>${status.charAt(0).toUpperCase() + status.slice(1)}`;
    editingRow.querySelector('td[data-label="Notes"]').innerText = notes;
    editingRow = null;
    modal.style.display = 'none';
    return;
  }

  const now = new Date();
  const formattedDate = `${now.getDate()} ${now.toLocaleString('default',{month:'long'})} ${now.getFullYear().toString().slice(-2)}, ${now.getHours()}${now.getHours()>=12?'pm':'am'}`;

  const row = document.createElement('tr');
  row.innerHTML = `
    <td data-label="Client Name">${client}</td>
    <td data-label="Last Touch">${formattedDate}</td>
    <td data-label="Status"><span class="status-dot status-${status}"></span>${status.charAt(0).toUpperCase() + status.slice(1)}</td>
    <td data-label="Notes">${notes}</td>
    <td data-label="Actions" class="action-cell">
        <span class="edit-lead-btn" title="Edit">✏️</span>
        <span class="convert-lead-btn" title="Convert to Client">🔄</span>
        <span class="delete-lead-btn" title="Delete">🗑️</span>
    </td>`;
  leadsTable.appendChild(row);
  modal.style.display = 'none';
});
</script>
