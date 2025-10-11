<?php
// Handle AJAX client creation
add_action('wp_ajax_create_realtor_client_ajax', 'handle_create_realtor_client_ajax');

function handle_create_realtor_client_ajax() {
    global $wpdb;
    
    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
    }

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cl_client_create_nonce')) {
        wp_send_json_error('Security verification failed');
    }

    $realtor_user_id = get_current_user_id(); // Current logged in realtor

    // Sanitize data
    $full_name = sanitize_text_field($_POST['realtor_client_full_name']);
    $email = sanitize_email($_POST['realtor_client_email']);
    $phone = sanitize_text_field($_POST['realtor_client_phone']);
    $preferred_location = sanitize_text_field($_POST['preferred_location']);
    $note = sanitize_textarea_field($_POST['realtor_client_note']);
    $status = sanitize_text_field($_POST['realtor_client_status']);
    $profile_picture_url = '';

    // Validate required fields
    if (empty($full_name) || empty($email) || empty($status)) {
        wp_send_json_error('Full name, email and status are required fields');
    }

    if (!is_email($email)) {
        wp_send_json_error('Please enter a valid email address');
    }

    // Check if email already exists in users table
    if (email_exists($email)) {
        wp_send_json_error('A user with this email address already exists');
    }

    // Check if email already exists in clients table for this realtor
    $existing_email = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}clients WHERE email = %s AND user_id = %d",
        $email,
        $realtor_user_id
    ));

    if ($existing_email > 0) {
        wp_send_json_error('A client with this email address already exists');
    }

    // Check if phone number already exists (if phone is provided)
    if (!empty($phone)) {
        $existing_phone = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}clients WHERE phone = %s AND user_id = %d",
            $phone,
            $realtor_user_id
        ));

        if ($existing_phone > 0) {
            wp_send_json_error('A client with this phone number already exists');
        }
    }

    // Generate username from email
    $username = generate_username_from_email($email);
    
    // Generate random password
    $password = wp_generate_password(12, true, true);

    // Create WordPress user account
    $client_user_id = wp_create_user($username, $password, $email);
    
    if (is_wp_error($client_user_id)) {
        wp_send_json_error('Failed to create user account: ' . $client_user_id->get_error_message());
    }

    // Set user role to 'client'
    $user = new WP_User($client_user_id);
    $user->set_role('client');

    // Update user meta with name
    $name_parts = split_full_name($full_name);
    update_user_meta($client_user_id, 'first_name', $name_parts['first_name']);
    update_user_meta($client_user_id, 'last_name', $name_parts['last_name']);
    update_user_meta($client_user_id, 'display_name', $full_name);

    // Handle profile picture upload
    if (!empty($_FILES['realtor_client_profile_picture']['name'])) {
        $upload_result = handle_profile_picture_upload($client_user_id);
        if ($upload_result && !is_wp_error($upload_result)) {
            $profile_picture_url = $upload_result['url'];
            // Also update user meta with profile picture
            update_user_meta($client_user_id, 'profile_picture', $profile_picture_url);
        }
    }

    // Insert into clients table
    $inserted = $wpdb->insert(
        $wpdb->prefix . 'clients',
        [
            'user_id' => $client_user_id, // Link to the new WordPress user
            'full_name' => $full_name,
            'email' => $email,
            'phone' => $phone,
            'preferred_location' => $preferred_location,
            'note' => $note,
            'status' => $status,
            'profile_picture' => $profile_picture_url,
            'created_by' => $realtor_user_id, // The realtor who created this client
            'created_at' => current_time('mysql')
        ],
        ['%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s']
    );

    if ($inserted === false) {
        // If client insertion fails, delete the WordPress user we just created
        wp_delete_user($client_user_id);
        wp_send_json_error('Failed to create client record. Please try again.');
    }

    // Send welcome email to client with login credentials
    send_welcome_email_to_client($email, $full_name, $username, $password);

    wp_send_json_success([
        'message' => 'Client created successfully! Login credentials sent to client\'s email.',
        'client_id' => $wpdb->insert_id,
        'user_id' => $client_user_id
    ]);
}

// Helper function to generate username from email
function generate_username_from_email($email) {
    $username = sanitize_user(explode('@', $email)[0]);
    $original_username = $username;
    $counter = 1;
    
    // Ensure username is unique
    while (username_exists($username)) {
        $username = $original_username . $counter;
        $counter++;
    }
    
    return $username;
}

// Helper function to split full name into first and last name
function split_full_name($full_name) {
    $name_parts = explode(' ', trim($full_name));
    $first_name = array_shift($name_parts);
    $last_name = implode(' ', $name_parts);
    
    return [
        'first_name' => $first_name,
        'last_name' => $last_name ?: $first_name // Use first name as last name if only one name provided
    ];
}

// Profile picture upload handler
function handle_profile_picture_upload($user_id) {
    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }
    
    $uploadedfile = $_FILES['realtor_client_profile_picture'];
    $upload_overrides = array('test_form' => false);
    
    $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
    
    if ($movefile && !isset($movefile['error'])) {
        return $movefile;
    }
    
    return false;
}

// Send welcome email to client
function send_welcome_email_to_client($email, $full_name, $username, $password) {
    $subject = 'Welcome to Our Platform - Your Account Details';
    
    $message = "
    Hello {$full_name},

    Your account has been created by your realtor. Here are your login details:

    Website: " . home_url() . "
    Username: {$username}
    Password: {$password}

    Please login and change your password after first login.

    Best regards,
    Your Real Estate Team
    ";

    $headers = array('Content-Type: text/plain; charset=UTF-8');
    
    wp_mail($email, $subject, $message, $headers);
}