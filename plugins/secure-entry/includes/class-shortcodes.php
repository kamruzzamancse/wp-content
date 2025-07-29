<?php
if (!defined('ABSPATH')) {
    exit;
}

class Enhanced_Login_Shortcodes {

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
        $this->init_shortcodes();
    }

    private function init_shortcodes() {
        add_shortcode('enhanced_login_form', array($this, 'login_form_shortcode'));
        add_shortcode('enhanced_register_form', array($this, 'register_form_shortcode'));
    }

    public function login_form_shortcode($atts) {
        if (is_user_logged_in()) {
            return $this->logged_in_message();
        }

        $atts = shortcode_atts(array(
            'redirect' => '',
        ), $atts);

        ob_start();
        $this->utilities->get_template('login-form.php', array(
            'redirect' => $atts['redirect']
        ));
        return ob_get_clean();
    }

    public function register_form_shortcode($atts) {
        if (is_user_logged_in()) {
            return $this->logged_in_message();
        }

        $atts = shortcode_atts(array(
            'redirect' => '',
        ), $atts);

        ob_start();
        $this->utilities->get_template('register-form.php', array(
            'redirect' => $atts['redirect']
        ));
        return ob_get_clean();
    }

    private function logged_in_message() {
        $current_user = wp_get_current_user();
        $message = sprintf(
            __('You are already logged in as %s. <a href="%s">Log out?</a>', 'enhanced-login'),
            $current_user->display_name,
            wp_logout_url(home_url())
        );
        return '<div class="enhanced-login-message">' . $message . '</div>';
    }
}