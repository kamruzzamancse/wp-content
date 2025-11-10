<?php
if (!defined('ABSPATH')) exit;

global $wpdb;

$clients_table    = $wpdb->prefix . 'clients';
$properties_table = $wpdb->prefix . 'rentcast_properties';
$assigned_table   = $wpdb->prefix . 'assigned_property';
$assigned_task_table = $wpdb->prefix . 'assigned_tasks';
$documents_table     = $wpdb->prefix . 'documents';

?>

<div class="assign-task-container">

    <h3>Assigned Task</h3>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Client Name</th>
                <th>Property Address</th>
                <th>Assigned Docs</th>
                <th>Assigned Date</th>
                <th>Reply Docs</th>
                <th>Reply Date</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody id="assigned-list">
        <?php

        $results = $wpdb->get_results("
            SELECT a.id, a.client_id, a.property_id, a.created_at,
                   c.full_name, p.address
            FROM {$assigned_table} a
            LEFT JOIN {$clients_table} c ON a.client_id = c.client_id
            LEFT JOIN {$properties_table} p ON a.property_id = p.id
            WHERE a.deleted_at IS NULL
            ORDER BY a.created_at DESC
        ");

        if ($results) {
            foreach ($results as $row) {

                // Fetch assigned task (document info)
                $task = $wpdb->get_row($wpdb->prepare("
                    SELECT t.document_id, t.created_at
                    FROM {$assigned_task_table} t
                    WHERE t.client_id = %d AND t.properties_id = %d
                    ORDER BY t.id DESC LIMIT 1
                ", $row->client_id, $row->property_id));

                $doc_name = '';
                $doc_date = '';

                if ($task && $task->document_id) {
                    $doc = $wpdb->get_row($wpdb->prepare("
                        SELECT title, file_name
                        FROM {$documents_table}
                        WHERE id = %d
                    ", $task->document_id));

                    if ($doc) {
                        // Only filename extracted (not full URL)
                        $file_short = basename($doc->file_name);
                        $doc_name = '<a href="'.esc_url($doc->file_name).'" target="_blank">'.$file_short.'</a>';
                        $doc_date = esc_html($task->created_at);
                    }
                }

                echo '<tr data-id="'.esc_attr($row->id).'" data-client-id="'.esc_attr($row->client_id).'" data-property-id="'.esc_attr($row->property_id).'">
                        <td>'.$row->full_name.'</td>
                        <td>'.$row->address.'</td>
                        <td>'.$doc_name.'</td>
                        <td>'.$doc_date.'</td>
                        <td></td>
                        <td></td>
                        <td>
                            <button class="button upload-document-trigger" 
                                data-assignment-id="'.$row->id.'"
                                data-client-id="'.$row->client_id.'"
                                data-property-id="'.$row->property_id.'">
                                <span class="dashicons dashicons-upload"></span>
                            </button>

                            <button class="button delete-assignment" 
                                data-assignment-id="'.$row->id.'">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </td>
                      </tr>';
            }

        } else {
            echo '<tr><td colspan="7">No assignments found.</td></tr>';
        }

        ?>
        </tbody>
    </table>
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
  background: #fff;
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
  min-width: 100px;
  max-width: 100px;
  text-align: center;
}

/* action icons */
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

/* Mobile responsiveness */
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
}
</style>