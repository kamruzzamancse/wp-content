<?php
if (!defined('ABSPATH')) {
    exit;
}

class Enhanced_Register_Handler {

    private static $instance = null;
    private $utilities;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->utilities = Enhanced_Login_Utilities::get_instance();
        $this->init_hooks();
    }

    private function init_hooks() {
        add_action('wp_ajax_enhanced_register', [$this, 'handle_registration']);
        add_action('wp_ajax_nopriv_enhanced_register', [$this, 'handle_registration']);
        add_filter('option_users_can_register', '__return_true');
    }

    public function handle_registration() {
        try {
            check_ajax_referer('enhanced-login-nonce', 'nonce');

            $data = $this->utilities->sanitize_array($_POST);
            $errors = $this->validate_registration_data($data);

            if (!empty($errors)) {
                throw new Exception(implode('<br>', $errors));
            }

            $user_id = wp_insert_user([
                'user_login' => $data['username'],
                'user_email' => $data['email'],
                'user_pass'  => $data['password'],
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'role'       => in_array($data['role'], ['realtor', 'client']) ? $data['role'] : 'client'
            ]);

            if (is_wp_error($user_id)) {
                throw new Exception($this->get_error_message($user_id->get_error_code()));
            }

            wp_new_user_notification($user_id, null, 'both');

            // Always redirect to login page after successful registration
            wp_send_json_success([
                'message' => __('Registration successful. Please log in.', 'enhanced-login'),
                'redirect' => home_url('/login/')
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    private function validate_registration_data($data) {
        $errors = [];

        // Username validation
        if (empty($data['username'])) {
            $errors[] = __('Username is required.', 'enhanced-login');
        } elseif (username_exists($data['username'])) {
            $errors[] = __('Username already exists.', 'enhanced-login');
        } elseif (!validate_username($data['username'])) {
            $errors[] = __('Invalid username.', 'enhanced-login');
        }

        // Email validation
        if (empty($data['email'])) {
            $errors[] = __('Email is required.', 'enhanced-login');
        } elseif (!is_email($data['email'])) {
            $errors[] = __('Invalid email address.', 'enhanced-login');
        } elseif (email_exists($data['email'])) {
            $errors[] = __('Email already exists.', 'enhanced-login');
        }

        // Password validation
        if (empty($data['password'])) {
            $errors[] = __('Password is required.', 'enhanced-login');
        } elseif (strlen($data['password']) < 6) {
            $errors[] = __('Password must be at least 6 characters.', 'enhanced-login');
        } elseif ($data['password'] !== $data['confirm_password']) {
            $errors[] = __('Passwords do not match.', 'enhanced-login');
        }

        return $errors;
    }

    private function get_error_message($error_code) {
        switch ($error_code) {
            case 'existing_user_login': return __('Username already exists.', 'enhanced-login');
            case 'existing_user_email': return __('Email already exists.', 'enhanced-login');
            default: return __('Registration failed. Please try again.', 'enhanced-login');
        }
    }

    // Keep this method as it might be used elsewhere
    private function get_redirect_url_by_role($role) {
        $urls = [
            'realtor' => site_url('/realtor-dashboard/'),
            'client' => site_url('/client-dashboard/'),
            'administrator' => admin_url()
        ];
        return $urls[$role] ?? home_url();
    }
}