<?php
if (!defined('ABSPATH')) exit;

global $wpdb;

// Table references
$clients_table               = $wpdb->prefix . 'clients';
$rentcast_properties_table   = $wpdb->prefix . 'rentcast_properties';
$assigned_property_table     = $wpdb->prefix . 'assigned_property';
$assigned_tasks_table        = $wpdb->prefix . 'assigned_tasks';
$documents_table             = $wpdb->prefix . 'documents';

?>

<div class="assign-task-container">

  <div class="assign-task-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
    <h3>Client Documents</h3>
    <div class="assign-task-filters" style="display:flex; gap:10px;">
        <input style="width:300px" type="text" id="assign-search" placeholder="Search client or address">
        <select id="assign-filter-status">
            <option value="">All Status</option>
            <option value="with_docs">With Docs</option>
            <option value="no_docs">Without Docs</option>
        </select>
        <button id="assign-filter-btn" class="button">Filter</button>
    </div>
  </div>

  <div id="assign-table-wrapper">
      <!-- Table will be loaded here via AJAX -->
  </div>

</div>

<?php include locate_template('dashboard-templates/rt/rt-upload-document-modal.php'); ?>

<style>
/* ===== Assign Task Page Styling ===== */
.assign-task-container {
  padding: 20px;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  font-family: Arial, sans-serif;
}

.assign-task-container table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  font-size: 14px;
  border: 1px solid #ddd;
  border-radius: 10px 10px 0 0;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.assign-task-container thead th {
  text-align: left;
  padding: 10px;
  border-bottom: 2px solid #ddd;
  font-weight: 600;
  color: #fff;
  background: #2271b1;
}

.assign-task-container tbody td {
  padding: 10px;
  border-bottom: 1px solid #eee;
  vertical-align: middle;
  max-width: 250px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.assign-task-container tbody tr:hover {
  background-color: #f5f9ff;
}

.assign-task-container tbody tr:last-child td {
  border-bottom: none;
}

.assign-task-container table th:first-child {
  border-top-left-radius: 10px;
}
.assign-task-container table th:last-child {
  border-top-right-radius: 10px;
  width: 100px;
  text-align: center;
}

/* Action buttons */
.assign-task-container .button {
  border: none;
  background: transparent;
  padding: 0;
  cursor: pointer;
  margin: 0 2.5px;
  font-size: 16px;
}

.assign-task-container tbody td:last-child {
  text-align: center;
}

table td:first-child {
    text-align: left;
}

/* ===== Mobile responsiveness ===== */
@media screen and (max-width: 768px) {
  .assign-task-container table,
  .assign-task-container thead,
  .assign-task-container tbody,
  .assign-task-container th,
  .assign-task-container tr {
    display: block;
    width: 100%;
  }

  .assign-task-container thead {
    display: none;
  }

  .assign-task-container tr {
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 12px;
    background: #f9f9ff;
  }

  .assign-task-container td {
    display: flex;
    flex-direction: column;
    width: 100%;
    padding: 8px 0;
    border: none;
    border-bottom: 1px solid #eee;
  }

  .assign-task-container td::before {
    content: attr(data-label);
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
  }

  .assign-task-container td:last-child {
    border-bottom: none;
    text-align: center;
  }
  .assign-task-header {
    flex-direction: column;
    align-items: flex-start !important;
    gap: 10px;
  }

  .assign-task-filters {
    flex-direction: column;
    width: 100%;
    gap: 10px;
  }

  .assign-task-filters input,
  .assign-task-filters select,
  .assign-task-filters button {
    width: 100% !important;
  }

  .assign-task-container tbody td:last-child {
    text-align: left;
  }

}
</style>