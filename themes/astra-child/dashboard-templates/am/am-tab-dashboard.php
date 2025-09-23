<div class="dashboard-top">

    <!-- LEFT SIDE -->
    <div class="dashboard-top-left">

        <!-- Active Clients Section -->
        <div class="dashboard-section active-clients-section">
          <div class="clients-header">
            <h1 class="header-title">Active Clients</h1>
            <button id="addClientBtn" class="btn-primary">+ Add Client</button>
          </div>

          <table class="active-clients-table">
              <thead>
                  <tr>
                      <th>Client Name</th>
                      <th>Address</th>
                      <th>Closing Date</th>
                      <th>Notes</th>
                      <th>Action</th>
                  </tr>
              </thead>
              <tbody>
                  <?php
                  $clients = ["Insurance Co", "Tech Solutions", "Green Energy", "HealthPlus", "Bright Finance", "Global Trade"];
                  $cities = ["New York", "Los Angeles", "Chicago", "Houston", "Miami", "San Francisco"];
                  $notesArr = [
                      "Follow-up required next week.",
                      "Client requested a meeting.",
                      "Pending documents.",
                      "Urgent response needed.",
                      "Initial contact completed.",
                      "Schedule demo session."
                  ];

                  for ($i = 0; $i < 6; $i++):
                      $clientName = $clients[array_rand($clients)];
                      $address = $cities[array_rand($cities)];
                      $closingDate = date("d F", strtotime("+".rand(1,60)." days"));
                      $notes = $notesArr[array_rand($notesArr)];
                  ?>
                  <tr>
                      <td data-label="Client Name"><?= $clientName ?></td>
                      <td data-label="Address"><?= $address ?></td>
                      <td data-label="Closing Date"><?= $closingDate ?></td>
                      <td data-label="Notes"><?= $notes ?></td>
                      <td data-label="Actions" class="action-cell">
                          <span class="delete-client-btn" title="Delete">🗑️</span>
                      </td>
                  </tr>
                  <?php endfor; ?>
              </tbody>
          </table>
        </div>

        <!-- Leads Section -->
        <div class="dashboard-section leads-section">
            <div class="leads-header">
                <h1 class="header-title">Leads</h1>
                <button id="addLeadBtn" class="btn-primary">+ Add Lead</button>
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
                    <tr>
                        <td data-label="Client Name">John Smith</td>
                        <td data-label="Last Touch">12 Sept 25, 3pm</td>
                        <td data-label="Status"><span class="status-dot status-hot"></span>Hot</td>
                        <td data-label="Notes">Contract update</td>
                        <td data-label="Actions" class="action-cell">
                        <span class="edit-lead-btn" title="Edit">✏️</span>
                        <span class="convert-lead-btn" title="Convert to Client">🔄</span>
                        <span class="delete-lead-btn" title="Delete">🗑️</span>
                        </td>
                    </tr>

                    <tr>
                        <td data-label="Client Name">Sarah Lee</td>
                        <td data-label="Last Touch">10 Sept 25, 11am</td>
                        <td data-label="Status"><span class="status-dot status-warm"></span>Warm</td>
                        <td data-label="Notes">Requested property list</td>
                        <td data-label="Actions" class="action-cell">
                        <span class="edit-lead-btn" title="Edit">✏️</span>
                        <span class="convert-lead-btn" title="Convert to Client">🔄</span>
                        <span class="delete-lead-btn" title="Delete">🗑️</span>
                        </td>
                    </tr>

                    <tr>
                        <td data-label="Client Name">Michael Brown</td>
                        <td data-label="Last Touch">8 Sept 25, 6pm</td>
                        <td data-label="Status"><span class="status-dot status-cold"></span>Cold</td>
                        <td data-label="Notes">Not responsive to emails</td>
                        <td data-label="Actions" class="action-cell">
                        <span class="edit-lead-btn" title="Edit">✏️</span>
                        <span class="convert-lead-btn" title="Convert to Client">🔄</span>
                        <span class="delete-lead-btn" title="Delete">🗑️</span>
                        </td>
                    </tr>

                    <tr>
                        <td data-label="Client Name">Emily Johnson</td>
                        <td data-label="Last Touch">5 Sept 25, 9am</td>
                        <td data-label="Status"><span class="status-dot status-hot"></span>Hot</td>
                        <td data-label="Notes">Wants to schedule site visit</td>
                        <td data-label="Actions" class="action-cell">
                        <span class="edit-lead-btn" title="Edit">✏️</span>
                        <span class="convert-lead-btn" title="Convert to Client">🔄</span>
                        <span class="delete-lead-btn" title="Delete">🗑️</span>
                        </td>
                    </tr>

                    <tr>
                        <td data-label="Client Name">David Wilson</td>
                        <td data-label="Last Touch">1 Sept 25, 4pm</td>
                        <td data-label="Status"><span class="status-dot status-warm"></span>Warm</td>
                        <td data-label="Notes">Requested mortgage options</td>
                        <td data-label="Actions" class="action-cell">
                        <span class="edit-lead-btn" title="Edit">✏️</span>
                        <span class="convert-lead-btn" title="Convert to Client">🔄</span>
                        <span class="delete-lead-btn" title="Delete">🗑️</span>
                        </td>
                    </tr>
                </tbody>

            </table>
        </div>

    </div>

    <!-- RIGHT SIDE -->
    <div class="dashboard-top-right">
        <?php
          $current_user = wp_get_current_user();
          $user_email   = $current_user->user_email;

          if ($user_email) {
              global $wpdb;
              $calendar_id = $wpdb->get_var($wpdb->prepare("
                  SELECT ID 
                  FROM $wpdb->posts 
                  WHERE post_type = 'calendar' 
                    AND post_status = 'publish'
                    AND post_title = %s
                  LIMIT 1
              ", $user_email));

              if ($calendar_id) {
                  echo do_shortcode('[calendar id="' . intval($calendar_id) . '"]');
              } else {
                  echo '<p>No calendar found for your account.</p>';
              }
          } else {
              echo '<p>Please login to see your calendar.</p>';
          }
        ?>

        <!-- Header -->
        <div class="notes-header">
            <h1>Notes</h1>
            <button class="add-note-btn">+</button>
        </div>

        <!-- Sticky Notes Container -->
        <div class="sticky-notes-container"></div>
    </div>
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
      <button id="saveLeadBtn" class="btn-primary">Save Lead</button>
    </div>
  </div>
</div>

<?php 
    include locate_template('dashboard-templates/rt/rt-client-create-modal.php');
?>

<style>
/* Primary button (Add & Save Lead) */
.btn-primary {
  background: #2271b1;
  color: #fff;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 14px;
  cursor: pointer;
  transition: background 0.2s ease;
}
.btn-primary:hover {
  background: #3c57c7;
}

/* Align Add Lead button to right */
.leads-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.leads-header .btn-primary {
  margin-left: auto;
}

/* Align Add Client button to right */
.clients-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.clients-header .btn-primary {
  margin-left: auto; /* Push button to right */
}

/* Modal Styling */
.lead-add-modal {
  display: none;
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.5);
  justify-content: center;
  align-items: center;
  z-index: 1000;
}
.lead-add-content {
  background: #fff;
  padding: 20px;
  border-radius: 10px;
  width: 400px;
  max-width: 90%;
}
.lead-add-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}
.close-lead-modal {
  cursor: pointer;
  font-size: 22px;
}
.action-cell span {
  cursor: pointer;
  margin: 0 4px;
  font-size: 18px;
  transition: transform 0.2s;
}
.action-cell span:hover {
  transform: scale(1.2);
}

/* Calendar styling */
.simcal-calendar {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    padding: 20px;
    margin-bottom: 20px;
}

#addLeadBtn, #saveLeadBtn {
    color: #fff!important;
}

/* Leads Table Styling */
.leads-table thead th:first-child {
    border-top-left-radius: 10px;
}
.leads-table thead th:last-child {
    border-top-right-radius: 10px;
}
.leads-table {
    border-collapse: separate;
    border-spacing: 0;
    border: 1px solid #ddd;
    border-radius: 10px;
    overflow: hidden;
}

/* Active Clients Table Styling */
.simcal-calendar-grid {
    border-collapse: separate;
    border-spacing: 0;
    border: 1px solid #ddd;
    border-radius: 10px;
    overflow: hidden;
}
.simcal-calendar-grid thead tr:first-child th:first-child {
    border-top-left-radius: 10px;
}
.simcal-calendar-grid thead tr:first-child th:last-child {
    border-top-right-radius: 10px;
}

/* ===== MOBILE VIEW ===== */
@media (max-width: 768px) {

  /* Hide table headers */
  .active-clients-table thead,
  .leads-table thead {
      display: none;
  }

  /* Make each row a block */
  .active-clients-table tr,
  .leads-table tr {
      display: block;
      margin-bottom: 12px;
      border: 1px solid #ddd;
      border-radius: 8px;
      overflow: hidden;
      padding: 8px 0;
  }

  /* Each cell becomes flex row */
  .active-clients-table td,
  .leads-table td {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 6px 12px;
      border-bottom: 1px solid #eee;
  }

  /* Remove border from last cell */
  .active-clients-table td:last-child,
  .leads-table td:last-child {
      border-bottom: 0;
  }

  /* Show data-label before value */
  .active-clients-table td::before,
  .leads-table td::before {
      content: attr(data-label);
      font-weight: 600;
      text-transform: uppercase;
      color: #555;
      margin-right: 10px;
      flex-shrink: 0;
  }

  /* + Add Lead button */
  #addLeadBtn {
      white-space: nowrap;
  }

  /* Action buttons spacing */
  .action-cell {
      display: flex;
      justify-content: flex-start;
      gap: 20px;
  }

  /* Status dots & text alignment fix */
  .leads-table td[data-label="Status"] {
      justify-content: flex-start;
  }

  .simcal-calendar {
    padding: 10px;
  }
}

/* Align Active Clients title and button in same line */
.active-clients-section {
    display: flex;
    flex-direction: column;
}

.active-clients-section .header-title {
    margin: 0;
    padding: 0;
    align-self: flex-start;
}

.active-clients-section .btn-primary {
    align-self: flex-end;
    margin-top: -28px; /* Adjust based on your title's line-height */
}

/* Ensure consistent styling with Add Lead button */
#addClientBtn {
    color: #fff !important;
}

/* Active Clients Table Actions column width */
.active-clients-table th:last-child,
.active-clients-table td:last-child {
    width: 100px;
    text-align: center;
}
</style>

<!-- ====== Scripts ====== -->
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

// actions
document.addEventListener('click', e => {
  if (e.target.closest('.edit-lead-btn')) {
    editingRow = e.target.closest('tr');
    document.getElementById('clientSelect').value = editingRow.querySelector('td[data-label="Client Name"]').innerText;
    document.getElementById('statusSelect').value = editingRow.querySelector('td[data-label="Status"]').innerText.trim().toLowerCase();
    document.getElementById('notesInput').value = editingRow.querySelector('td[data-label="Notes"]').innerText;
    modal.style.display = 'flex';
  }
  if (e.target.closest('.delete-lead-btn')) {
    if (confirm("Are you sure you want to delete this lead?")) {
      e.target.closest('tr').remove();
    }
  }
  if (e.target.closest('.convert-lead-btn')) {
    alert("Lead converted to Client (frontend demo only).");
    e.target.closest('tr').remove();
  }
});

// save
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

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const openBtn = document.getElementById("addClientBtn");  
    const modal = document.getElementById("rmRealtorClientCreateModal");  
    const closeBtn = document.getElementById("closeRealtorClientCreateModal");  

    // Open modal
    openBtn.addEventListener("click", () => {
      modal.style.display = "flex";   // or use a CSS class like .active
    });

    // Close modal with X
    closeBtn.addEventListener("click", () => {
      modal.style.display = "none";
    });

    // Close on outside click
    window.addEventListener("click", (e) => {
      if (e.target === modal) {
        modal.style.display = "none";
      }
    });
  });
</script>

<!-- Add this inside your <script> tag or before </body> -->
<script>
document.addEventListener('click', function(e) {
    if (e.target.closest('.delete-client-btn')) {
        if (confirm("Are you sure you want to delete this client?")) {
            e.target.closest('tr').remove();
        }
    }
});
</script>