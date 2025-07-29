<?php
/**
 * Plugin Name: Enhanced Login & Registration
 * Description: A custom login and registration system for WordPress.
 * Version: 1.0.0
 * Author: Md. Kamruzzaman
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: enhanced-login
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ENHANCED_LOGIN_VERSION', '1.0.0');
define('ENHANCED_LOGIN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ENHANCED_LOGIN_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once ENHANCED_LOGIN_PLUGIN_DIR . 'includes/class-utilities.php';
require_once ENHANCED_LOGIN_PLUGIN_DIR . 'includes/class-shortcodes.php';
require_once ENHANCED_LOGIN_PLUGIN_DIR . 'includes/class-login-handler.php';
require_once ENHANCED_LOGIN_PLUGIN_DIR . 'includes/class-register-handler.php';

class Enhanced_Login_Registration {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Initialize plugin components
        $this->init_hooks();
        $this->init_classes();
    }

    private function init_hooks() {
        // Register activation/deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Load text domain
        add_action('plugins_loaded', array($this, 'load_textdomain'));

        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('login_enqueue_scripts', array($this, 'enqueue_scripts'));

        // Add settings link
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_settings_link'));
    }

    private function init_classes() {
        Enhanced_Login_Shortcodes::get_instance();
        Enhanced_Login_Handler::get_instance();
        Enhanced_Register_Handler::get_instance();
    }

    public function activate() {
        // Activation code here
        flush_rewrite_rules();
    }

    public function deactivate() {
        // Deactivation code here
        flush_rewrite_rules();
    }

    public function load_textdomain() {
        load_plugin_textdomain('enhanced-login', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    public function enqueue_scripts() {
        // CSS
        wp_enqueue_style(
            'enhanced-login-css',
            ENHANCED_LOGIN_PLUGIN_URL . 'assets/css/style.css',
            array(),
            ENHANCED_LOGIN_VERSION
        );

        // JS
        wp_enqueue_script(
            'enhanced-login-js',
            ENHANCED_LOGIN_PLUGIN_URL . 'assets/js/script.js',
            array('jquery', 'password-strength-meter'),
            ENHANCED_LOGIN_VERSION,
            true
        );

        // Localize script for AJAX
        wp_localize_script(
            'enhanced-login-js',
            'enhanced_login_vars',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('enhanced-login-nonce')
            )
        );
    }

    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('options-general.php?page=enhanced-login-settings') . '">' . __('Settings', 'enhanced-login') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
}

// Initialize the plugin
Enhanced_Login_Registration::get_instance();