<?php
/**
 * Plugin Name: Posts Grid & Search Widgets Pro
 * Plugin URI: https://example.com
 * Description: Professional posts grid widget with Elementor integration, advanced search functionality, ACF filters, and comprehensive styling options.
 * Version: 2.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: posts-grid-search
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('PGS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PGS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('PGS_VERSION', '2.0.0');

class PostsGridSearchPluginPro {
    
    public function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Check WordPress version compatibility
        if (version_compare(get_bloginfo('version'), '5.0', '<')) {
            add_action('admin_notices', array($this, 'wordpress_version_notice'));
            return;
        }
        
        // Load text domain for translations
        load_plugin_textdomain('posts-grid-search', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Register widgets
        add_action('widgets_init', array($this, 'register_widgets'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Add admin menu for plugin settings
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Add plugin action links
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_action_links'));
        
        // Initialize filters
        $this->init_filters();
    }
    
    public function activate() {
        // Set default options
        add_option('pgs_version', PGS_VERSION);
        add_option('pgs_settings', array(
            'enable_elementor_integration' => true,
            'enable_acf_integration' => true,
            'cache_duration' => 3600
        ));
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        // Clean up temporary data
        delete_transient('pgs_elementor_templates');
        flush_rewrite_rules();
    }
    
    public function wordpress_version_notice() {
        echo '<div class="notice notice-error"><p>';
        echo __('Posts Grid & Search Widgets Pro requires WordPress 5.0 or higher.', 'posts-grid-search');
        echo '</p></div>';
    }
    
    public function register_widgets() {
        register_widget('PGS_Posts_Grid_Widget');
        register_widget('PGS_Search_Filter_Widget');
        register_widget('PGS_ACF_Loop_Grid_Filters_Widget');
    }
    
    public function enqueue_frontend_assets() {
        wp_enqueue_style(
            'pgs-frontend-style', 
            PGS_PLUGIN_URL . 'assets/css/frontend.css', 
            array(), 
            PGS_VERSION
        );
        
        wp_enqueue_script(
            'pgs-frontend-script', 
            PGS_PLUGIN_URL . 'assets/js/frontend.js', 
            array('jquery'), 
            PGS_VERSION, 
            true
        );
        
        // Localize script for AJAX with enhanced data
        wp_localize_script('pgs-frontend-script', 'pgs_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pgs_nonce'),
            'loading_text' => __('Loading...', 'posts-grid-search'),
            'error_text' => __('Something went wrong. Please try again.', 'posts-grid-search'),
            'no_results_text' => __('No posts found.', 'posts-grid-search')
        ));
    }
    
    public function enqueue_admin_assets($hook) {
        if ($hook === 'widgets.php' || strpos($hook, 'pgs-settings') !== false) {
            wp_enqueue_style(
                'pgs-admin-style', 
                PGS_PLUGIN_URL . 'assets/css/admin.css', 
                array(), 
                PGS_VERSION
            );
            
            wp_enqueue_script(
                'pgs-admin-script', 
                PGS_PLUGIN_URL . 'assets/js/admin.js', 
                array('jquery'), 
                PGS_VERSION, 
                true
            );
            
            // Enqueue media scripts for advanced features
            wp_enqueue_media();
            
            // Localize admin script
            wp_localize_script('pgs-admin-script', 'pgs_admin', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('pgs_admin_nonce'),
                'elementor_active' => class_exists('\Elementor\Plugin'),
                'acf_active' => class_exists('ACF')
            ));
        }
    }
    
    public function add_admin_menu() {
        add_options_page(
            __('Posts Grid Pro Settings', 'posts-grid-search'),
            __('Posts Grid Pro', 'posts-grid-search'),
            'manage_options',
            'pgs-settings',
            array($this, 'admin_page')
        );
    }
    
    public function add_action_links($links) {
        $settings_link = '<a href="' . admin_url('options-general.php?page=pgs-settings') . '">' . __('Settings', 'posts-grid-search') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
    
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Posts Grid & Search Widgets Pro', 'posts-grid-search'); ?></h1>
            
            <div class="pgs-admin-header">
                <p><?php _e('Professional WordPress widgets for displaying posts with advanced filtering and styling options.', 'posts-grid-search'); ?></p>
            </div>
            
            <div class="pgs-admin-cards">
                <div class="pgs-admin-card">
                    <h3><?php _e('🎨 Posts Grid Widget', 'posts-grid-search'); ?></h3>
                    <p><?php _e('Display posts in customizable grid layouts with Elementor template integration.', 'posts-grid-search'); ?></p>
                    <ul>
                        <li>✅ <?php _e('Multiple built-in templates', 'posts-grid-search'); ?></li>
                        <li>✅ <?php _e('Elementor saved templates support', 'posts-grid-search'); ?></li>
                        <li>✅ <?php _e('Advanced styling options', 'posts-grid-search'); ?></li>
                        <li>✅ <?php _e('Responsive design', 'posts-grid-search'); ?></li>
                    </ul>
                </div>
                
                <div class="pgs-admin-card">
                    <h3><?php _e('🔍 Search Filter Widget', 'posts-grid-search'); ?></h3>
                    <p><?php _e('Real-time search functionality with professional styling options.', 'posts-grid-search'); ?></p>
                    <ul>
                        <li>✅ <?php _e('Real-time search with debounce', 'posts-grid-search'); ?></li>
                        <li>✅ <?php _e('Custom styling options', 'posts-grid-search'); ?></li>
                        <li>✅ <?php _e('Mobile responsive', 'posts-grid-search'); ?></li>
                        <li>✅ <?php _e('AJAX pagination support', 'posts-grid-search'); ?></li>
                    </ul>
                </div>
                
                <div class="pgs-admin-card">
                    <h3><?php _e('🎯 ACF Loop Grid Filters', 'posts-grid-search'); ?></h3>
                    <p><?php _e('Advanced filtering based on ACF fields and publish dates.', 'posts-grid-search'); ?></p>
                    <ul>
                        <li>✅ <?php _e('ACF field filtering', 'posts-grid-search'); ?></li>
                        <li>✅ <?php _e('Date range filtering', 'posts-grid-search'); ?></li>
                        <li>✅ <?php _e('Pagination support', 'posts-grid-search'); ?></li>
                        <li>✅ <?php _e('Professional styling', 'posts-grid-search'); ?></li>
                    </ul>
                </div>
            </div>
            
            <div class="pgs-admin-info">
                <h3><?php _e('Plugin Information', 'posts-grid-search'); ?></h3>
                <table class="form-table">
                    <tr>
                        <th><?php _e('Version:', 'posts-grid-search'); ?></th>
                        <td><?php echo PGS_VERSION; ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Elementor Integration:', 'posts-grid-search'); ?></th>
                        <td><?php echo class_exists('\Elementor\Plugin') ? '✅ Active' : '❌ Not Available'; ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('ACF Integration:', 'posts-grid-search'); ?></th>
                        <td><?php echo class_exists('ACF') ? '✅ Active' : '❌ Not Available'; ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <style>
        .pgs-admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .pgs-admin-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .pgs-admin-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #14b8a6;
        }
        
        .pgs-admin-card h3 {
            margin-top: 0;
            color: #1a202c;
        }
        
        .pgs-admin-card ul {
            list-style: none;
            padding: 0;
        }
        
        .pgs-admin-card li {
            padding: 5px 0;
            font-size: 14px;
        }
        
        .pgs-admin-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        </style>
        <?php
    }
    
    private function init_filters() {
        // Include the filters class
        require_once PGS_PLUGIN_PATH . 'includes/filters.php';
    }
}

// Initialize the plugin
new PostsGridSearchPluginPro();

// Include widget classes
require_once PGS_PLUGIN_PATH . 'includes/class-posts-grid-widget.php';
require_once PGS_PLUGIN_PATH . 'includes/class-search-filter-widget.php';
require_once PGS_PLUGIN_PATH . 'includes/class-acf-loop-grid-filters-widget.php';

// Add professional admin notices
add_action('admin_notices', function() {
    if (get_transient('pgs_activation_notice')) {
        echo '<div class="notice notice-success is-dismissible">';
        echo '<p><strong>' . __('Posts Grid & Search Widgets Pro', 'posts-grid-search') . '</strong> ' . __('has been activated successfully!', 'posts-grid-search') . '</p>';
        echo '<p>' . __('Go to Appearance → Widgets to start using your new professional widgets.', 'posts-grid-search') . '</p>';
        echo '</div>';
        delete_transient('pgs_activation_notice');
    }
});

// Set activation notice
register_activation_hook(__FILE__, function() {
    set_transient('pgs_activation_notice', true, 30);
});