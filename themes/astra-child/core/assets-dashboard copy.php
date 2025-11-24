<?php
if (!defined('ABSPATH')) exit;

/**
 * Unified Asset Management System
 */

class MDK_Asset_Manager {
    
    private $script_handles = [];
    
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_all_assets']);
    }
    
    /**
     * Main asset enqueue method
     */
    public function enqueue_all_assets() {
        $this->enqueue_dashboard_assets();
        $this->enqueue_conditional_scripts();
    }
    
    /**
     * Dashboard-specific assets
     */
    private function enqueue_dashboard_assets() {
        global $post;
        
        if (!is_admin() && is_a($post, 'WP_Post')) {
            $dashboard_slugs = ['realtor-dashboard', 'admin-dashboard', 'client-dashboard'];
            
            if (in_array($post->post_name, $dashboard_slugs)) {
                $this->enqueue_asset('mdk-dashboard-style', 'assets/css/rt-dashboard.css', 'css');
                $this->enqueue_asset('mdk-dashboard-script', 'assets/js/rt-dashboard.js', 'js', ['jquery']);
            }
        }
    }
    
    /**
     * Conditional scripts based on page and tab
     */
    private function enqueue_conditional_scripts() {
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : '';
        $current_page = get_post_field('post_name', get_the_ID());
        
        $script_configs = [
            // Client Dashboard Scripts
            [
                'condition' => $current_page === 'client-dashboard' && $current_tab === 'documents',
                'handle' => 'cl-reply-docs-js',
                'path' => 'assets/js/cl-reply-docs.js',
                'localize' => [
                    'object_name' => 'rtReplyDocsAjax',
                    'data' => [
                        'ajax_url' => admin_url('admin-ajax.php'),
                        'nonce' => wp_create_nonce('cl_reply_docs_nonce')
                    ]
                ]
            ],
            
            // Realtor Dashboard Scripts
            [
                'condition' => $current_page === 'realtor-dashboard' && $current_tab === 'assign-task',
                'handle' => 'rt-ap-assign-task-js',
                'path' => 'assets/js/rt-ap-assign-task.js',
                'localize' => [
                    'object_name' => 'rtAssignTaskAjax',
                    'data' => [
                        'ajax_url' => admin_url('admin-ajax.php'),
                        'nonce' => wp_create_nonce('rt_ap_assign_task_nonce')
                    ]
                ]
            ],
            
            // Add all other script configurations here...
            // [condition, handle, path, localize_data]
        ];
        
        foreach ($script_configs as $config) {
            if ($config['condition']) {
                $this->enqueue_asset($config['handle'], $config['path'], 'js', ['jquery']);
                
                if (isset($config['localize'])) {
                    wp_localize_script($config['handle'], $config['localize']['object_name'], $config['localize']['data']);
                }
            }
        }
        
        // Special cases with external dependencies
        $this->enqueue_special_scripts($current_page, $current_tab);
    }
    
    /**
     * Handle special scripts with external dependencies
     */
    private function enqueue_special_scripts($current_page, $current_tab) {
        // Address Book Scripts
        if (($current_page === 'realtor-dashboard' || $current_page === 'admin-dashboard') && 
            in_array($current_tab, ['address-book', 'clients', ''])) {
            
            $this->enqueue_external_script('sheetjs', 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js', '0.18.5');
            $this->enqueue_asset('rt-ab-client-js', 'assets/js/rt-ab-client.js', 'js', ['jquery', 'sheetjs']);
            
            wp_localize_script('rt-ab-client-js', 'rtClientAjax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'create_nonce' => wp_create_nonce('rt_client_create_nonce'),
                'edit_nonce' => wp_create_nonce('rt_client_edit_nonce'),
                'delete_nonce' => wp_create_nonce('rt_client_delete_nonce'),
                'export_nonce' => wp_create_nonce('rt_client_export_nonce'),
                'import_nonce' => wp_create_nonce('rt_client_import_nonce'),
                'default_avatar' => get_stylesheet_directory_uri() . '/assets/images/default-avatar.jpg',
                'default_property_image' => get_stylesheet_directory_uri() . '/assets/images/default-property.png',
                'debug' => true,
            ]);
        }
        
        // Profile Scripts
        $this->enqueue_profile_scripts($current_tab);
        
        // Password Scripts
        $this->enqueue_password_scripts($current_tab);
    }
    
    /**
     * Unified asset enqueue helper
     */
    private function enqueue_asset($handle, $path, $type, $deps = []) {
        $full_path = get_stylesheet_directory() . '/' . $path;
        $uri = get_stylesheet_directory_uri() . '/' . $path;
        
        if (!file_exists($full_path)) return false;
        
        $version = filemtime($full_path);
        
        if ($type === 'css') {
            wp_enqueue_style($handle, $uri, $deps, $version);
        } else {
            wp_enqueue_script($handle, $uri, $deps, $version, true);
        }
        
        $this->script_handles[] = $handle;
        return true;
    }
    
    /**
     * External script enqueue
     */
    private function enqueue_external_script($handle, $url, $version, $deps = []) {
        wp_enqueue_script($handle, $url, $deps, $version, true);
        $this->script_handles[] = $handle;
    }
    
    /**
     * Profile scripts handler
     */
    private function enqueue_profile_scripts($current_tab) {
        $profile_scripts = [
            'realtor' => [
                'tabs' => ['rt-settings-pi', 'rt-settings-pi-edit'],
                'handle' => 'rt-profile-script',
                'path' => 'assets/js/rt-realtor-profile.js',
                'localize' => [
                    'object_name' => 'rt_profile_ajax',
                    'nonce' => 'rt_profile_nonce'
                ]
            ],
            'client' => [
                'tabs' => ['cl-settings-pi', 'cl-settings-pi-edit'],
                'handle' => 'cl-profile-script',
                'path' => 'assets/js/cl-client-profile.js',
                'localize' => [
                    'object_name' => 'cl_profile_ajax',
                    'nonce' => 'cl_profile_nonce'
                ]
            ]
        ];
        
        foreach ($profile_scripts as $role => $config) {
            if (in_array($current_tab, $config['tabs'])) {
                $this->enqueue_asset($config['handle'], $config['path'], 'js', ['jquery']);
                wp_localize_script($config['handle'], $config['localize']['object_name'], [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce($config['localize']['nonce'])
                ]);
            }
        }
        
        // Document scripts
        if ($current_tab === 'documents' || $current_tab === 'address-book') {
            $this->enqueue_asset('rt-document-type-script', 'assets/js/rt-document-type.js', 'js', ['jquery']);
            $this->enqueue_asset('rt-documents-script', 'assets/js/rt-documents.js', 'js', ['jquery']);
            
            wp_localize_script('rt-document-type-script', 'rt_doc_type_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('rt_doc_type_nonce')
            ]);
            
            wp_localize_script('rt-documents-script', 'rt_doc_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('rt_doc_nonce')
            ]);
        }
    }
    
    /**
     * Password scripts handler
     */
    private function enqueue_password_scripts($current_tab) {
        $password_scripts = [
            'realtor' => [
                'tab' => 'rt-settings-cp',
                'handle' => 'rt-password-script',
                'path' => 'assets/js/rt-settings-password.js',
                'localize' => [
                    'object_name' => 'rt_password_ajax',
                    'nonce' => 'rt_password_nonce'
                ]
            ],
            'client' => [
                'tab' => 'cl-settings-cp',
                'handle' => 'cl-password-script',
                'path' => 'assets/js/cl-settings-password.js',
                'localize' => [
                    'object_name' => 'cl_password_ajax',
                    'nonce' => 'cl_password_nonce'
                ]
            ]
        ];
        
        foreach ($password_scripts as $role => $config) {
            if ($current_tab === $config['tab']) {
                $this->enqueue_asset($config['handle'], $config['path'], 'js', ['jquery']);
                wp_localize_script($config['handle'], $config['localize']['object_name'], [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce($config['localize']['nonce'])
                ]);
            }
        }
    }
    
    /**
     * Get all registered script handles (for debugging)
     */
    public function get_script_handles() {
        return $this->script_handles;
    }
}

// Initialize the asset manager
new MDK_Asset_Manager();