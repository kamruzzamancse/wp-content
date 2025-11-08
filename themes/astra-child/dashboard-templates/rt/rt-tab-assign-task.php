<?php
if (!defined('ABSPATH')) exit;

global $wpdb;

$clients_table       = $wpdb->prefix . 'clients';
$properties_table    = $wpdb->prefix . 'rentcast_properties';
$assigned_property   = $wpdb->prefix . 'assigned_property';
$assigned_tasks      = $wpdb->prefix . 'assigned_tasks';
$documents_table     = $wpdb->prefix . 'documents';

/*
    FINAL MERGED LOGIC
    1. wp_assigned_property is always the base table
    2. wp_assigned_tasks overrides it if available (document + reply)
*/
$query = "
    SELECT 
        ap.id AS assign_id,
        ap.client_id,
        ap.property_id,
        ap.created_at AS assigned_date,

        c.full_name,
        p.address,

        at.document_id,
        at.reply_document_id,
        at.created_at AS task_created_at,
        at.updated_at AS reply_date,

        d1.file_name AS assigned_doc_file,
        d1.title AS assigned_doc_title,

        d2.file_name AS reply_doc_file,
        d2.title AS reply_doc_title

    FROM $assigned_property ap
    LEFT JOIN $clients_table c ON ap.client_id = c.client_id
    LEFT JOIN $properties_table p ON ap.property_id = p.id

    LEFT JOIN $assigned_tasks at ON at.client_id = ap.client_id 
        AND at.property_id = ap.property_id 
        AND at.deleted_at IS NULL

    LEFT JOIN $documents_table d1 ON d1.id = at.document_id
    LEFT JOIN $documents_table d2 ON d2.id = at.reply_document_id

    WHERE ap.deleted_at IS NULL
    ORDER BY ap.created_at DESC
";

$results = $wpdb->get_results($query);
?>

<div class="assign-property-container">

    <h3>Assigned Tasks</h3>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Client Name</th>
                <th>Property Address</th>
                <th>Assigned Documents</th>
                <th>Assigned Date</th>
                <th>Reply Documents</th>
                <th>Reply Date</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody id="assigned-list">
            <?php
            if ($results) {
                foreach ($results as $row) {

                    // Assigned Document link/text
                    $assigned_doc = $row->assigned_doc_file 
                        ? "<a href='" . esc_url(wp_upload_dir()['baseurl'] . '/' . $row->assigned_doc_file) . "' target='_blank'>" . esc_html($row->assigned_doc_title) . "</a>"
                        : "<span style='color:#999'>No document</span>";

                    // Reply Document link/text
                    $reply_doc = $row->reply_doc_file 
                        ? "<a href='" . esc_url(wp_upload_dir()['baseurl'] . '/' . $row->reply_doc_file) . "' target='_blank'>" . esc_html($row->reply_doc_title) . "</a>"
                        : "<span style='color:#999'>No reply</span>";

                    echo "
                    <tr data-id='{$row->assign_id}' data-client-id='{$row->client_id}' data-property-id='{$row->property_id}'>
                        <td>{$row->full_name}</td>
                        <td>{$row->address}</td>
                        <td>{$assigned_doc}</td>
                        <td>{$row->assigned_date}</td>
                        <td>{$reply_doc}</td>
                        <td>{$row->reply_date}</td>

                        <td style='text-align:center'>
                            <button class='button upload-doc-btn' 
                                data-assign-id='{$row->assign_id}' 
                                data-client-id='{$row->client_id}' 
                                data-property-id='{$row->property_id}'
                                title='Upload Document'>📤</button>

                            <button class='button edit-assignment' 
                                data-assignment-id='{$row->assign_id}' 
                                data-client-id='{$row->client_id}' 
                                data-property-id='{$row->property_id}'
                                title='Edit Assignment'>✏️</button>

                            <button class='button delete-assignment' 
                                data-assignment-id='{$row->assign_id}'
                                title='Delete Assignment'>🗑️</button>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No assigned tasks found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php 
include locate_template('dashboard-templates/rt/rt-upload-document-modal.php');
?>

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
  justify-content: flex-end;
  flex-wrap: wrap;
  gap: 15px;
  margin-bottom: 20px;
}

/* --- Each search field box --- */
.assign-field {
  display: flex;
  flex-direction: column;
  width: 200px;
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
  background: #2271b1;
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

/* --- Action Buttons - Clean Icons Only --- */
.assign-property-container .button {
  border: none;
  background: transparent;
  padding: 0;
  cursor: pointer;
  margin: 0 2.5px;
  font-size: 16px;
}

.assign-property-container tbody td:last-child {
  text-align: center;
}

/* --- Edit Assignment Modal Styles --- */
#edit-assignment-modal {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 9999;
  overflow-y: auto;
}

#edit-assignment-modal .clup-box {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: white;
  border-radius: 10px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  animation: modalSlideIn 0.3s ease-out;
}

#edit-assignment-modal .assign-field {
  width: 100%;
  margin-bottom: 20px;
}

.clup-cancel {
  background: #6c757d !important;
}

.clup-cancel:hover {
  background: #5a6268 !important;
}

/* --- Upload Document Modal Styles --- */
#cl-upload-document-modal {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 9999;
  overflow-y: auto;
}

.clup-box {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: white;
  border-radius: 10px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  animation: modalSlideIn 0.3s ease-out;
}

.clup-close-btn {
  position: absolute;
  top: 15px;
  right: 20px;
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #666;
  z-index: 10;
}

.clup-close-btn:hover {
  color: #000;
}

.clup-title {
  padding: 20px;
  margin: 0;
  font-size: 20px;
  font-weight: 600;
  color: #222;
  border-bottom: 1px solid #eee;
}

.clup-form {
  padding: 20px;
}

.clup-row-single {
  margin-bottom: 20px;
}

.clup-field label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #333;
}

.clup-field input[type="text"],
.clup-field select {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 14px;
  box-sizing: border-box;
}

.clup-field input[type="text"]:focus,
.clup-field select:focus {
  outline: none;
  border-color: #007bff;
  box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

.clup-upload-box {
  border: 2px dashed #ddd;
  border-radius: 8px;
  padding: 30px;
  text-align: center;
  margin-bottom: 20px;
  transition: border-color 0.3s ease;
}

.clup-upload-box:hover {
  border-color: #007bff;
}

.clup-upload-content .clup-upload-icon {
  font-size: 40px;
  margin-bottom: 10px;
}

.clup-upload-content p {
  margin: 0 0 5px 0;
  font-weight: 600;
  color: #333;
}

.clup-upload-content span {
  color: #666;
  font-size: 12px;
  display: block;
  margin-bottom: 15px;
}

.clup-browse {
  background: #007bff;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

.clup-browse:hover {
  background: #0056b3;
}

#selected-file-name {
  color: #007bff;
  font-weight: 500;
  margin-top: 10px;
}

.clup-actions {
  text-align: right;
  padding-top: 20px;
  border-top: 1px solid #eee;
}

.clup-btn {
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: background-color 0.3s ease;
}

.clup-upload {
  background: #007bff;
  color: white;
}

.clup-upload:hover {
  background: #0056b3;
}

/* --- Responsive Design --- */
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
    text-align: center;
  }

  .assign-property-container td:last-child .button {
    display: inline-block;
    margin: 0 5px;
  }

  .assign-form {
    justify-content: flex-start;
  }

  #assign-btn {
    width: 100%;
  }

  .clup-box {
    width: 95%;
    margin: 20px auto;
  }
  
  .clup-form {
    padding: 15px;
  }
  
  .clup-upload-box {
    padding: 20px;
  }
}
</style>
