<?php
if (!defined('ABSPATH')) {
    exit;
}

class Enhanced_Login_Handler {

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
        // Handle login form submission
        add_action('wp_ajax_enhanced_login', array($this, 'handle_login'));
        add_action('wp_ajax_nopriv_enhanced_login', array($this, 'handle_login'));

        // Add login errors
        add_filter('login_errors', array($this, 'filter_login_errors'));
    }

    public function handle_login() {
        check_ajax_referer('enhanced-login-nonce', 'nonce');

        $data = $this->utilities->sanitize_array($_POST);

        $creds = array(
            'user_login'    => $data['username'],
            'user_password' => $data['password'],
            'remember'      => isset($data['rememberme']),
        );

        $user = wp_signon($creds, is_ssl());

        if (is_wp_error($user)) {
            $field = '';
            if (in_array($user->get_error_code(), ['empty_username', 'invalid_username'])) {
                $field = 'username';
            } elseif (in_array($user->get_error_code(), ['empty_password', 'incorrect_password'])) {
                $field = 'password';
            }
            
            wp_send_json_error(array(
                'message' => $this->get_error_message($user->get_error_code()),
                'field' => $field
            ));
        }

        // Get all user roles in lowercase for comparison
        $user_data = get_userdata($user->ID);
        $user_roles = array_map('strtolower', $user_data->roles);
        
        // Determine redirect URL based on role
        $redirect_url = $this->get_redirect_url_by_role($user_roles);
        
        // If a specific redirect was passed in the form, use that instead
        if (!empty($data['redirect']) && filter_var($data['redirect'], FILTER_VALIDATE_URL)) {
            $redirect_url = esc_url_raw($data['redirect']);
        }

        wp_send_json_success(array(
            'redirect' => esc_url_raw($redirect_url)
        ));
    }

    private function get_error_message($error_code) {
        switch ($error_code) {
            case 'invalid_username':
                return __('Invalid username or email.', 'enhanced-login');
            case 'incorrect_password':
                return __('Incorrect password. Please try again.', 'enhanced-login');
            case 'empty_username':
                return __('Username or email is required.', 'enhanced-login');
            case 'empty_password':
                return __('Password is required.', 'enhanced-login');
            default:
                return __('Login failed. Please try again.', 'enhanced-login');
        }
    }

    public function filter_login_errors($error) {
        // Don't reveal whether the username or password was incorrect
        if (strpos($error, 'incorrect') !== false) {
            $error = __('Invalid username or password.', 'enhanced-login');
        }
        return $error;
    }

    private function get_redirect_url_by_role($roles) {
        // Ensure roles array is properly formatted
        if (!is_array($roles)) {
            $roles = (array)$roles;
        }
        
        $roles = array_map('strtolower', $roles);
        
        $redirect_urls = apply_filters('enhanced_login_redirect_urls', [
            'administrator' => admin_url(),
            'realtor'      => home_url('/realtor-dashboard/'),
            'client'       => home_url('/client-dashboard/'),
            'subscriber'   => home_url('/my-account/')
        ]);
        
        foreach ($roles as $role) {
            if (isset($redirect_urls[$role])) {
                return $redirect_urls[$role];
            }
        }
        
        return home_url();
    }
}