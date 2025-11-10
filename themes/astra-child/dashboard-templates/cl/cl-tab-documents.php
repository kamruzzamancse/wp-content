<?php
if (!defined('ABSPATH')) exit;

global $wpdb;

$clients_table        = $wpdb->prefix . 'clients';
$properties_table     = $wpdb->prefix . 'rentcast_properties';
$assigned_table       = $wpdb->prefix . 'assigned_property';
$assigned_task_table  = $wpdb->prefix . 'assigned_tasks';
$documents_table      = $wpdb->prefix . 'documents';
$reply_docs_table     = $wpdb->prefix . 'reply_docs';

$current_user_id = get_current_user_id();
?>

<div class="documents-container">

    <h3>Documents</h3>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Property</th>
                <th>Assigned Docs</th>
                <th>Assigned Date</th>
                <th>Reply Docs</th>
                <th>Reply Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="assigned-list">

        <?php
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT a.id, a.client_id, a.property_id, a.created_at,
                   c.full_name, p.address
            FROM {$assigned_table} a
            LEFT JOIN {$clients_table} c ON a.client_id = c.client_id
            LEFT JOIN {$properties_table} p ON a.property_id = p.id
            WHERE a.deleted_at IS NULL
              AND c.user_id = %d
            ORDER BY a.created_at DESC
        ", $current_user_id));

        if ($results) {
            foreach ($results as $row) {

                // Assigned Task & Document
                $task = $wpdb->get_row($wpdb->prepare("
                    SELECT t.id AS task_id, t.document_id, t.created_at
                    FROM {$assigned_task_table} t
                    WHERE t.client_id = %d AND t.properties_id = %d
                    ORDER BY t.id DESC LIMIT 1
                ", $row->client_id, $row->property_id));

                $assigned_doc_name = '';
                $assigned_doc_date = '';
                if ($task && $task->document_id) {
                    $doc = $wpdb->get_row($wpdb->prepare("
                        SELECT title, file_name
                        FROM {$documents_table}
                        WHERE id = %d
                    ", $task->document_id));

                    if ($doc) {
                        $short = basename($doc->file_name);
                        $assigned_doc_name = '<a href="'.esc_url($doc->file_name).'" target="_blank">'.$short.'</a>';
                        $assigned_doc_date = esc_html($task->created_at);
                    }
                }

                // Reply Document
                $reply = null;
                $reply_doc_name = '';
                $reply_doc_date = '';
                $reply_doc_id_attr = '';

                if ($task) {
                    $reply = $wpdb->get_row($wpdb->prepare("
                        SELECT r.id, r.document_id, r.created_at
                        FROM {$reply_docs_table} r
                        WHERE r.assigned_task_id = %d AND r.deleted_at IS NULL
                        ORDER BY r.id DESC LIMIT 1
                    ", $task->task_id));

                    if ($reply && $reply->document_id) {
                        $reply_doc = $wpdb->get_row($wpdb->prepare("
                            SELECT title, file_name
                            FROM {$documents_table}
                            WHERE id = %d
                        ", $reply->document_id));

                        if ($reply_doc) {
                            $short = basename($reply_doc->file_name);
                            $reply_doc_name = '<a href="'.esc_url($reply_doc->file_name).'" target="_blank">'.$short.'</a>';
                            $reply_doc_date = esc_html($reply->created_at);
                        }

                        $reply_doc_id_attr = 'data-reply-doc-id="'.esc_attr($reply->id).'"';
                    }
                }

                echo '<tr data-id="'.esc_attr($row->id).'"
                          data-client-id="'.esc_attr($row->client_id).'"
                          data-property-id="'.esc_attr($row->property_id).'">

                        <td>'.$row->address.'</td>
                        <td>'.$assigned_doc_name.'</td>
                        <td>'.$assigned_doc_date.'</td>
                        <td>'.$reply_doc_name.'</td>
                        <td>'.$reply_doc_date.'</td>

                        <td>
                            <button class="button upload-document-trigger"
                                data-assignment-id="'.$row->id.'"
                                data-client-id="'.$row->client_id.'"
                                data-property-id="'.$row->property_id.'"
                                data-assigned-task-id="'.($task ? $task->task_id : 0).'">
                                <span class="dashicons dashicons-upload"></span>
                            </button>

                            <button class="button delete-assignment"
                                data-assignment-id="'.$row->id.'"
                                '.$reply_doc_id_attr.'>
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </td>
                      </tr>';
            }
        } else {
            echo '<tr><td colspan="6">No assignments found.</td></tr>';
        }
        ?>

        </tbody>
    </table>
</div>

<?php include locate_template('dashboard-templates/cl/cl-upload-document-modal.php'); ?>

<style>
/* ===== Assign Task Page Styling ===== */
.documents-container {
  padding: 20px;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  font-family: Arial, sans-serif;
}

.documents-container table {
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

.documents-container thead th {
  text-align: left;
  padding: 10px;
  border-bottom: 2px solid #ddd;
  font-weight: 600;
  color: #fff;
  background: #2271b1;
}

.documents-container tbody td {
  padding: 10px;
  border-bottom: 1px solid #eee;
  vertical-align: middle;
  max-width: 250px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.documents-container tbody tr:hover {
  background-color: #f5f9ff;
}

.documents-container tbody tr:last-child td {
  border-bottom: none;
}

.documents-container table th:first-child {
  border-top-left-radius: 10px;
}
.documents-container table th:last-child {
  border-top-right-radius: 10px;
  width: 100px;
  min-width: 100px;
  max-width: 100px;
  text-align: center;
}

/* action icons */
.documents-container .button {
  border: none;
  background: transparent;
  padding: 0;
  cursor: pointer;
  margin: 0 2.5px;
  font-size: 16px;
}

.documents-container tbody td:last-child {
  text-align: center;
}

/* Mobile responsiveness */
@media screen and (max-width: 768px) {
  .documents-container table,
  .documents-container thead,
  .documents-container tbody,
  .documents-container th,
  .documents-container tr {
    display: block;
    width: 100%;
  }

  .documents-container thead {
    display: none;
  }

  .documents-container tr {
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 12px;
    background: #f9f9ff;
  }

  .documents-container td {
    display: flex;
    flex-direction: column;
    width: 100%;
    padding: 8px 0;
    border: none;
    border-bottom: 1px solid #eee;
  }

  .documents-container td::before {
    content: attr(data-label);
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
  }

  .documents-container td:last-child {
    border-bottom: none;
    text-align: center;
  }
}
</style>