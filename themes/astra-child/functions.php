<?php
/**
 * Recommended way to include parent theme styles.
 * (Please see http://codex.wordpress.org/Child_Themes#How_to_Create_a_Child_Theme)
 *
 */  

add_action( 'wp_enqueue_scripts', 'astra_child_style' );
				function astra_child_style() {
					wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
					wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array('parent-style') );
				}

/**
 * Your code goes below.
 */


// Shortcode for displaying Realtor Dashboard
function mdk_realtor_dashboard() {
    // Ensure the user is logged in and is a Realtor
    if (!is_user_logged_in() || !current_user_can('realtor')) {
        // Return the message with a login link
        return 'You need to be logged in as a Realtor to view this page. <a href="' . home_url('/login/') . '">Login here</a>';
    }

    ob_start();
    ?>
    <div class="mdk-realtor-dashboard">
        <h2>Welcome to Your Realtor Dashboard</h2>
        <p>Here you can manage your properties, assign tasks, and view client information.</p>
        
        <!-- Display Realtor's Properties -->
        <h3>Your Properties</h3>
        <?php
        // Fetch assigned properties for Realtor
        $user_id = get_current_user_id();
        $properties = get_user_meta($user_id, 'assigned_property', false);

        if (!empty($properties)) {
            echo '<ul>';
            foreach ($properties as $property) {
                echo '<li>' . esc_html($property) . '</li>';
            }
            echo '</ul>';
        } else {
            echo 'You have no properties assigned.';
        }
        ?>

        <!-- Display Realtor's Tasks -->
        <h3>Your Tasks</h3>
        <?php
        // Fetch tasks for the Realtor
        $tasks = get_user_meta($user_id, 'assigned_tasks', false);

        if (!empty($tasks)) {
            echo '<ul>';
            foreach ($tasks as $task) {
                echo '<li>' . esc_html($task) . '</li>';
            }
            echo '</ul>';
        } else {
            echo 'You have no tasks assigned.';
        }
        ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('mdk_realtor_dashboard', 'mdk_realtor_dashboard');



// Shortcode for displaying Client Dashboard
function mdk_client_dashboard() {
    // Ensure the user is logged in and is a Client
    if (!is_user_logged_in() || !current_user_can('client')) {
        return 'You need to be logged in as a Client to view this page. <a href="' . home_url('/login/') . '">Login here</a>';
    }

    ob_start();
    ?>
    <div class="mdk-client-dashboard">
        <h2>Welcome to Your Client Dashboard</h2>
        <p>Here you can view your assigned tasks and properties.</p>
        
        <!-- Display Client's Properties -->
        <h3>Your Assigned Properties</h3>
        <?php
        // Fetch assigned properties for Client
        $user_id = get_current_user_id();
        $properties = get_user_meta($user_id, 'assigned_property', false);

        if (!empty($properties)) {
            echo '<ul>';
            foreach ($properties as $property) {
                echo '<li>' . esc_html($property) . '</li>';
            }
            echo '</ul>';
        } else {
            echo 'You have no properties assigned.';
        }
        ?>

        <!-- Display Client's Tasks -->
        <h3>Your Tasks</h3>
        <?php
        // Fetch tasks for the Client
        $tasks = get_user_meta($user_id, 'assigned_tasks', false);

        if (!empty($tasks)) {
            echo '<ul>';
            foreach ($tasks as $task) {
                echo '<li>' . esc_html($task) . '</li>';
            }
            echo '</ul>';
        } else {
            echo 'You have no tasks assigned.';
        }
        ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('mdk_client_dashboard', 'mdk_client_dashboard');

