<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$clients_table    = $wpdb->prefix . 'clients';
$properties_table = $wpdb->prefix . 'rentcast_properties';
$assigned_table   = $wpdb->prefix . 'assigned_property';
?>

<div class="assign-property-container">
    <h2>Assign Property to Client</h2>

    <div class="assign-form">
        <!-- Client Search -->
        <div class="assign-field">
            <label for="client-search">Search Client:</label>
            <input type="text" id="client-search" placeholder="Type client name..." autocomplete="off">
            <input type="hidden" id="client-id">
            <div id="client-suggestions" class="suggestion-box"></div>
        </div>

        <!-- Property Search -->
        <div class="assign-field">
            <label for="property-search">Search Property:</label>
            <input type="text" id="property-search" placeholder="Type property address..." autocomplete="off">
            <input type="hidden" id="property-id">
            <div id="property-suggestions" class="suggestion-box"></div>
        </div>

        <button id="assign-btn" class="button button-primary">Assign Property</button>
    </div>

    <hr>

    <!-- Assigned Properties Table -->
    <h3>Assigned Properties</h3>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Client Name</th>
                <th>Property Address</th>
                <th>Assigned Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="assigned-list">
            <?php
            $results = $wpdb->get_results(
                $wpdb->prepare("
                    SELECT a.id, c.full_name, p.address, a.created_at
                    FROM {$assigned_table} a
                    LEFT JOIN {$clients_table} c ON a.client_id = c.client_id
                    LEFT JOIN {$properties_table} p ON a.property_id = p.id
                    WHERE a.deleted_at IS NULL
                    ORDER BY a.created_at DESC
                ")
            );

            if ($results) {
                foreach ($results as $row) {
                    echo "<tr data-id='" . esc_attr($row->id) . "'>
                            <td>" . esc_html($row->full_name) . "</td>
                            <td>" . esc_html($row->address) . "</td>
                            <td>" . esc_html($row->created_at) . "</td>
                            <td>
                                <button class='button view-assignment'>View</button>
                                <button class='button edit-assignment'>Edit</button>
                                <button class='button delete-assignment'>Delete</button>
                            </td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No assignments found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<style>
/* ===== Assign Property Page Styling ===== */
.assign-property-container {
  padding: 20px;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  font-family: Arial, sans-serif;
}

/* --- Page Title --- */
.assign-property-container h2 {
  font-size: 22px;
  font-weight: 600;
  margin-bottom: 20px;
  color: #222;
}

/* --- Form Section --- */
.assign-form {
  display: flex;
  align-items: flex-end;
  justify-content: flex-end; /* ✅ align everything to the right */
  flex-wrap: wrap;
  gap: 15px;
  margin-bottom: 20px;
}

/* --- Each search field box --- */
.assign-field {
  display: flex;
  flex-direction: column;
  width: 200px; /* fixed width */
  position: relative;
}

.assign-field label {
  font-weight: 600;
  margin-bottom: 6px;
  display: block;
  color: #333;
}

.assign-field input[type="text"] {
  width: 100%;
  padding: 8px 10px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 14px;
}

/* --- Suggestion box --- */
.suggestion-box {
  position: absolute;
  top: 100%;
  left: 0;
  width: 100%;
  background: #fff;
  border: 1px solid #ccc;
  border-radius: 6px;
  margin-top: 2px;
  display: none;
  z-index: 10;
  max-height: 200px;
  overflow-y: auto;
}

.suggestion-box div {
  padding: 8px 10px;
  cursor: pointer;
  font-size: 14px;
}
.suggestion-box div:hover {
  background-color: #f0f0f0;
}

/* --- Assign button styling --- */
#assign-btn {
  background-color: #007bff;
  color: #fff !important;
  border: none;
  border-radius: 6px;
  padding: 13px 20px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.3s ease;
}

#assign-btn:hover {
  background-color: #0056b3;
}

/* --- Assigned Properties Table --- */
.assign-property-container table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  background: #fff;
  font-size: 14px;
  border: 1px solid #ddd;
  border-radius: 10px 10px 0 0;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.assign-property-container thead th {
  text-align: left;
  padding: 10px;
  border-bottom: 2px solid #ddd;
  font-weight: 600;
  color: #fff;
  background: #2271b1; /* header background color */
}

.assign-property-container tbody td {
  padding: 10px;
  border-bottom: 1px solid #eee;
  vertical-align: middle;
  max-width: 250px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.assign-property-container tbody tr:hover {
  background-color: #f5f9ff;
}

.assign-property-container tbody tr:last-child td {
  border-bottom: none;
}

.assign-property-container table th:first-child {
  border-top-left-radius: 10px;
}
.assign-property-container table th:last-child {
  border-top-right-radius: 10px;
  width: 100px;
  min-width: 100px;
  max-width: 100px;
  text-align: center;
}

/* --- Action Buttons --- */
.assign-property-container .button {
  font-size: 13px;
  border-radius: 5px;
  padding: 6px 10px;
  cursor: pointer;
  transition: background-color 0.25s, transform 0.2s;
}

.assign-property-container .button.view-assignment {
  background-color: #17a2b8;
  color: #fff;
  border: none;
}
.assign-property-container .button.edit-assignment {
  background-color: #ffc107;
  color: #000;
  border: none;
}
.assign-property-container .button.delete-assignment {
  background-color: #dc3545;
  color: #fff;
  border: none;
}

/* --- Responsive Table --- */
@media screen and (max-width: 768px) {
  .assign-property-container table,
  .assign-property-container thead,
  .assign-property-container tbody,
  .assign-property-container th,
  .assign-property-container tr {
    display: block;
    width: 100%;
  }

  .assign-property-container thead {
    display: none;
  }

  .assign-property-container tr {
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 12px;
    background: #f9f9ff;
  }

  .assign-property-container td {
    display: flex;
    flex-direction: column;
    width: 100%;
    padding: 8px 0;
    border: none;
    border-bottom: 1px solid #eee;
  }

  .assign-property-container td::before {
    content: attr(data-label);
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
  }

  .assign-property-container td:last-child {
    border-bottom: none;
  }

  .assign-property-container td .button {
    width: 100%;
    margin-top: 6px;
  }

  /* Make form stack vertically on mobile */
  .assign-form {
    justify-content: flex-start;
  }

  #assign-btn {
    width: 100%;
  }
}

</style>