<?php
if (!defined('ABSPATH')) {
    exit;
}

class Enhanced_Login_Utilities {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get_template($template_name, $args = array()) {
        if (!empty($args) && is_array($args)) {
            extract($args);
        }

        $template_path = ENHANCED_LOGIN_PLUGIN_DIR . 'templates/' . $template_name;

        if (file_exists($template_path)) {
            include $template_path;
        } else {
            _doing_it_wrong(__FUNCTION__, sprintf('<code>%s</code> does not exist.', $template_path), '1.0.0');
        }
    }

    public function is_password_strong($password) {
        // At least 8 characters
        if (strlen($password) < 8) {
            return false;
        }

        // At least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }

        // At least one lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }

        // At least one number
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }

        // At least one special character
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            return false;
        }

        return true;
    }

    public function sanitize_array($array) {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->sanitize_array($value);
            } else {
                $array[$key] = sanitize_text_field($value);
            }
        }
        return $array;
    }

    public function redirect($location, $status = 302) {
        wp_safe_redirect($location, $status);
        exit;
    }
}