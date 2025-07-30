<?php
class Todo_Calendar {
    public function __construct() {
        add_shortcode('todo_calendar', array($this, 'render_calendar'));
        add_action('wp_ajax_todo_calendar_save', array($this, 'ajax_save_todo'));
        add_action('wp_ajax_todo_calendar_delete', array($this, 'ajax_delete_todo'));
        add_action('wp_ajax_todo_calendar_get', array($this, 'ajax_get_todos'));
        add_action('wp_ajax_todo_calendar_view', array($this, 'ajax_view_todo'));
    }

    public function render_calendar() {
        ob_start();
        ?>
        <div class="todo-calendar-container">
            <div class="calendar-header">
                <h2 class="calendar-title"><?php echo date_i18n('F Y'); ?></h2>
                <div class="calendar-actions">
                    <button class="calendar-prev" data-month="-1">&lt;</button>
                    <button class="calendar-next" data-month="+1">&gt;</button>
                </div>
            </div>
            <table class="calendar-grid">
                <thead>
                    <tr>
                        <?php foreach (array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat') as $day): ?>
                            <th><?php echo $day; ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody class="calendar-body">
                    <!-- Days populated via JavaScript -->
                </tbody>
            </table>
            
            <!-- Add Todo Modal -->
            <div class="todo-form-modal" style="display:none;">
                <div class="modal-content">
                    <h3>Add Todo</h3>
                    <input type="hidden" id="todo-date">
                    <textarea id="todo-content" placeholder="Enter your todo..."></textarea>
                    <div class="modal-actions">
                        <button class="button save-todo">Save</button>
                        <button class="button cancel-todo">Cancel</button>
                    </div>
                </div>
            </div>
            
            <!-- View Todo Modal -->
            <div class="todo-view-modal" style="display:none;">
                <div class="modal-content">
                    <h3>Todo Details</h3>
                    <div class="todo-detail-content"></div>
                    <div class="modal-actions">
                        <button class="button close-todo">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function ajax_save_todo() {
        try {
            $this->verify_request();
            
            $date = sanitize_text_field($_POST['date']);
            $raw_content = $_POST['content'] ?? '';
            $user_id = get_current_user_id();
            
            // Validate inputs
            if (empty($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                throw new Exception(__('Invalid date format', 'astra-child'), 400);
            }
            
            if (empty($raw_content)) {
                throw new Exception(__('Todo content cannot be empty', 'astra-child'), 400);
            }
            
            // Prepare content
            $content = array(
                'full' => sanitize_textarea_field($raw_content),
                'display' => $this->truncate_content($raw_content),
                'created' => current_time('timestamp'),
                'modified' => current_time('timestamp')
            );
            
            // Update todos
            $todos = get_user_meta($user_id, 'user_todos', true) ?: array();
            $todos[$date][] = $content;
            
            if (!update_user_meta($user_id, 'user_todos', $todos)) {
                throw new Exception(__('Failed to save todo', 'astra-child'), 500);
            }
            
            wp_send_json_success(array(
                'message' => __('Todo saved successfully', 'astra-child'),
                'content' => $content
            ));
            
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ), $e->getCode() ?: 500);
        }
    }

    public function ajax_delete_todo() {
        try {
            $this->verify_request();
            
            $date = sanitize_text_field($_POST['date']);
            $index = intval($_POST['index']);
            $user_id = get_current_user_id();
            
            $todos = get_user_meta($user_id, 'user_todos', true) ?: array();
            
            if (!isset($todos[$date][$index])) {
                throw new Exception(__('Todo not found', 'astra-child'), 404);
            }
            
            unset($todos[$date][$index]);
            $todos[$date] = array_values($todos[$date]);
            
            if (empty($todos[$date])) {
                unset($todos[$date]);
            }
            
            if (!update_user_meta($user_id, 'user_todos', $todos)) {
                throw new Exception(__('Failed to update todos', 'astra-child'), 500);
            }
            
            wp_send_json_success(__('Todo deleted successfully', 'astra-child'));
            
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ), $e->getCode() ?: 500);
        }
    }

    public function ajax_get_todos() {
        $this->verify_request();
        $user_id = get_current_user_id();
        $todos = get_user_meta($user_id, 'user_todos', true) ?: array();
        wp_send_json_success($todos);
    }

    public function ajax_view_todo() {
        try {
            $this->verify_request();
            
            $date = sanitize_text_field($_POST['date']);
            $index = intval($_POST['index']);
            $user_id = get_current_user_id();
            
            $todos = get_user_meta($user_id, 'user_todos', true) ?: array();
            
            if (!isset($todos[$date][$index])) {
                throw new Exception(__('Todo not found', 'astra-child'), 404);
            }
            
            $todo = $todos[$date][$index];
            $content = is_array($todo) ? ($todo['full'] ?? $todo) : $todo;
            
            wp_send_json_success(array(
                'content' => wp_kses_post($content)
            ));
            
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ), $e->getCode() ?: 500);
        }
    }

    private function verify_request() {
        check_ajax_referer('todo_calendar_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(__('You must be logged in', 'astra-child'), 401);
        }
    }

    private function truncate_content($content, $length = 50) {
        $content = sanitize_text_field($content);
        return strlen($content) > $length 
            ? substr($content, 0, $length - 3) . '...' 
            : $content;
    }
}