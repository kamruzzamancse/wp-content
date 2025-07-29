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
        // Handle registration form submission
        add_action('wp_ajax_enhanced_register', array($this, 'handle_registration'));
        add_action('wp_ajax_nopriv_enhanced_register', array($this, 'handle_registration'));

        // Enable user registration (in case it's disabled in WordPress settings)
        add_filter('option_users_can_register', '__return_true');
    }

    public function handle_registration() {
        check_ajax_referer('enhanced-login-nonce', 'nonce');

        $data = $this->utilities->sanitize_array($_POST);

        // Validate data
        $errors = $this->validate_registration_data($data);

        if (!empty($errors)) {
            wp_send_json_error(array(
                'message' => implode('<br>', $errors)
            ));
        }

        // Validate user role
        $allowed_roles = array('realtor', 'client');
        $user_role = in_array($data['role'], $allowed_roles) ? $data['role'] : 'client';

        // Create user
        $user_id = wp_insert_user(array(
            'user_login' => $data['username'],
            'user_email' => $data['email'],
            'user_pass'  => $data['password'],
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'role'       => $user_role
        ));

        if (is_wp_error($user_id)) {
            wp_send_json_error(array(
                'message' => $this->get_error_message($user_id->get_error_code())
            ));
        }

        // Send notification
        wp_new_user_notification($user_id, null, 'both');

        // Log the user in
        $creds = array(
            'user_login'    => $data['username'],
            'user_password' => $data['password'],
            'remember'      => true
        );

        $user = wp_signon($creds, is_ssl());

        if (is_wp_error($user)) {
            wp_send_json_error(array(
                'message' => __('Registration successful but login failed. Please try logging in.', 'enhanced-login')
            ));
        }

        // Determine redirect URL based on role
        $redirect_url = $this->get_redirect_url_by_role($user_role);
        
        // If a specific redirect was passed in the form, use that instead
        if (!empty($data['redirect'])) {
            $redirect_url = $data['redirect'];
        }

        wp_send_json_success(array(
            'redirect' => $redirect_url
        ));
    }

    private function validate_registration_data($data) {
        $errors = array();

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

        // Role validation
        if (empty($data['role'])) {
            $errors[] = __('Please select a role.', 'enhanced-login');
        } elseif (!in_array($data['role'], array('realtor', 'client'))) {
            $errors[] = __('Invalid role selected.', 'enhanced-login');
        }

        // Password validation
        if (empty($data['password'])) {
            $errors[] = __('Password is required.', 'enhanced-login');
        } elseif (strlen($data['password']) < 8) {
            $errors[] = __('Password must be at least 8 characters long.', 'enhanced-login');
        } elseif (!$this->utilities->is_password_strong($data['password'])) {
            $errors[] = __('Password must contain uppercase, lowercase, number and special character.', 'enhanced-login');
        } elseif ($data['password'] !== $data['confirm_password']) {
            $errors[] = __('Passwords do not match.', 'enhanced-login');
        }

        return $errors;
    }

    private function get_error_message($error_code) {
        switch ($error_code) {
            case 'existing_user_login':
                return __('Username already exists.', 'enhanced-login');
            case 'existing_user_email':
                return __('Email already exists.', 'enhanced-login');
            default:
                return __('Registration failed. Please try again.', 'enhanced-login');
        }
    }

    private function get_redirect_url_by_role($role) {
        $redirect_urls = array(
            'realtor' => site_url('/realtor-dashboard/'),
            'client'  => site_url('/client-dashboard/')
        );
        
        return $redirect_urls[$role] ?? home_url();
    }
}