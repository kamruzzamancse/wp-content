<div class="cld-task-section">
    <div class="cld-task-header">
        <h2 class="header-title">Documents</h2>
    </div>

    <div class="documents-section">
        <table class="documents-table grouped-doc-table">
            <thead>
                <tr>
                    <th style="width:100px;">Profile</th>
                    <th>Realtor</th>
                    <th>Documents</th>
                    <th style="width:80px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                global $wpdb;
                $assigned_table = $wpdb->prefix . 'assigned_tasks';
                $realtor_table  = $wpdb->prefix . 'realtors';
                $documents_table = $wpdb->prefix . 'documents';

                // Fetch all assigned documents
                $tasks = $wpdb->get_results("
                    SELECT at.id AS assigned_id, at.document_id,
                           r.user_id, r.full_name AS realtor_name, r.profile_picture AS realtor_pic,
                           d.title AS document_title, d.file_name AS document_file
                    FROM $assigned_table at
                    LEFT JOIN $realtor_table r ON at.created_by = r.user_id AND r.deleted_at IS NULL
                    LEFT JOIN $documents_table d ON at.document_id = d.id AND d.deleted_at IS NULL
                    WHERE at.deleted_at IS NULL
                    ORDER BY r.user_id, at.created_at DESC
                ");

                if ($tasks):
                    $grouped = [];

                    // Group by realtor
                    foreach ($tasks as $task) {
                        $rid = $task->user_id ?: 0;
                        if (!isset($grouped[$rid])) {
                            $grouped[$rid] = [
                                "realtor_name" => $task->realtor_name,
                                "realtor_pic" => $task->realtor_pic,
                                "docs" => []
                            ];
                        }

                        $grouped[$rid]["docs"][] = [
                            "file" => $task->document_file,
                            "name" => $task->document_file ? basename($task->document_file) : null
                        ];
                    }

                    foreach ($grouped as $realtor):
                        ?>
                        <tr>
                            <td rowspan="<?php echo count($realtor['docs']); ?>" class="rt-profile-cell">
                                <?php if ($realtor['realtor_pic']): ?>
                                    <img src="<?php echo esc_url($realtor['realtor_pic']); ?>" class="rt-avatar">
                                <?php else: ?>
                                    <span>—</span>
                                <?php endif; ?>
                            </td>

                            <td rowspan="<?php echo count($realtor['docs']); ?>" class="rt-name-cell">
                                <?php echo esc_html($realtor['realtor_name'] ?: "—"); ?>
                            </td>

                            <?php
                            $first = true;
                            foreach ($realtor['docs'] as $doc):
                                if (!$first) echo "<tr>"; // new row for each additional doc

                                $file_url = $doc["file"];
                                $file_name = $doc["name"];
                                $file_path = str_replace(wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], $file_url);
                            ?>

                                <td><?php echo esc_html($file_name ?: "—"); ?></td>
                                <td class="action-cell">
                                    <?php if ($file_url && file_exists($file_path)): ?>
                                        <a href="<?php echo esc_url($file_url); ?>" download="<?php echo esc_attr($file_name); ?>" title="Download">⬇️</a>
                                    <?php else: ?>
                                        <span style="color:red;">Missing</span>
                                    <?php endif; ?>
                                </td>

                            </tr>
                            <?php $first = false; endforeach; ?>

                        <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align:center;">No Documents Found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<style>

/* Container */
.cld-task-section {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    margin-bottom: 20px;
}

/* Table */
.grouped-doc-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    border: 1px solid #ddd;
    border-radius: 10px 10px 0 0;
    overflow: hidden;
}

.grouped-doc-table th {
    background: #2271b1;
    color: #fff;
    padding: 12px;
}

.grouped-doc-table td {
    padding: 12px;
    vertical-align: middle;
    border-bottom: 1px solid #eee;
}

/* Profile image */
.rt-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    display: block;
    margin: 0 auto;
}

/* Action column */
.action-cell {
    text-align: center;
    display: flex;
    justify-content: center;
    gap: 8px;
}

/* Responsive */
@media (max-width: 768px) {
    .grouped-doc-table thead { display: none; }
    .grouped-doc-table, .grouped-doc-table tbody, .grouped-doc-table tr, .grouped-doc-table td {
        display: block;
        width: 100%;
    }
    .grouped-doc-table tr {
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 10px;
    }
}

</style>
