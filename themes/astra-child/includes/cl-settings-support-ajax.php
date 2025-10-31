<?php
if (!defined('ABSPATH')) exit;

/**
 * Handle Client Support Message Sending via AJAX
 */
function cl_send_support_message() {
    // Verify AJAX nonce
    check_ajax_referer('cl_support_nonce', 'nonce');

    // Sanitize form inputs
    $name    = sanitize_text_field($_POST['name'] ?? '');
    $email   = sanitize_email($_POST['email'] ?? '');
    $phone   = sanitize_text_field($_POST['phone'] ?? '');
    $message = sanitize_textarea_field($_POST['message'] ?? '');

    // Validate required fields
    if (empty($name) || empty($email) || empty($message)) {
        wp_send_json_error('Please fill in all required fields correctly.');
    }

    // Admin email
    $admin_email = get_option('admin_email');

    // Email subject and body
    $subject = 'New Client Support Message from ' . $name;
    $body = "You have received a new support message:\n\n"
          . "Name: $name\n"
          . "Email: $email\n"
          . "Phone: $phone\n\n"
          . "Message:\n$message\n\n"
          . "Sent from your website: " . home_url();

    // Email headers
    $headers = [
        'Content-Type: text/plain; charset=UTF-8',
        "Reply-To: $name <$email>"
    ];

    // Send email
    $sent = wp_mail($admin_email, $subject, $body, $headers);

    // Return JSON response
    if ($sent) {
        wp_send_json_success('Your message has been sent successfully.');
    } else {
        wp_send_json_error('Sorry, there was a problem sending your message.');
    }
}

// Register AJAX actions
add_action('wp_ajax_cl_send_support_message', 'cl_send_support_message');
add_action('wp_ajax_nopriv_cl_send_support_message', 'cl_send_support_message');
