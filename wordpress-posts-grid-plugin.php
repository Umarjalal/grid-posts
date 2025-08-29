<?php
/**
 * Plugin Name: Posts Grid & Search Widgets
 * Plugin URI: https://example.com
 * Description: Advanced posts grid widget with search functionality, multiple templates, and customizable pagination.
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: posts-grid-search
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('PGS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PGS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('PGS_VERSION', '1.0.0');

class PostsGridSearchPlugin {
    
    public function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    public function init() {
        // Load text domain for translations
        load_plugin_textdomain('posts-grid-search', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Register widgets
        add_action('widgets_init', array($this, 'register_widgets'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // AJAX handlers
        add_action('wp_ajax_pgs_filter_posts', array($this, 'ajax_filter_posts'));
        add_action('wp_ajax_nopriv_pgs_filter_posts', array($this, 'ajax_filter_posts'));
    }
    
    public function register_widgets() {
        register_widget('PGS_Posts_Grid_Widget');
        register_widget('PGS_Search_Filter_Widget');
    }
    
    public function enqueue_frontend_assets() {
        wp_enqueue_style('pgs-frontend-style', PGS_PLUGIN_URL . 'assets/css/frontend.css', array(), PGS_VERSION);
        wp_enqueue_script('pgs-frontend-script', PGS_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), PGS_VERSION, true);
        
        // Localize script for AJAX
        wp_localize_script('pgs-frontend-script', 'pgs_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pgs_nonce')
        ));
    }
    
    public function enqueue_admin_assets($hook) {
        if ($hook === 'widgets.php') {
            wp_enqueue_style('pgs-admin-style', PGS_PLUGIN_URL . 'assets/css/admin.css', array(), PGS_VERSION);
            wp_enqueue_script('pgs-admin-script', PGS_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), PGS_VERSION, true);
            wp_enqueue_media(); // For icon selection
        }
    }
    
    public function ajax_filter_posts() {
        check_ajax_referer('pgs_nonce', 'nonce');
        
        $search_query = sanitize_text_field($_POST['search_query']);
        $posts_per_page = intval($_POST['posts_per_page']);
        $template = sanitize_text_field($_POST['template']);
        $page = intval($_POST['page']);
        
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $posts_per_page,
            'paged' => $page,
        );
        
        if (!empty($search_query)) {
            $args['s'] = $search_query;
        }
        
        $query = new WP_Query($args);
        
        ob_start();
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $this->render_post_template($template);
            }
        } else {
            echo '<div class="pgs-no-posts">' . __('No posts found.', 'posts-grid-search') . '</div>';
        }
        wp_reset_postdata();
        
        $posts_html = ob_get_clean();
        
        wp_send_json_success(array(
            'posts' => $posts_html,
            'total_pages' => $query->max_num_pages,
            'current_page' => $page
        ));
    }
    
    private function render_post_template($template) {
        $post_id = get_the_ID();
        $title = get_the_title();
        $excerpt = get_the_excerpt();
        $author = get_the_author();
        $date = get_the_date();
        $thumbnail = get_the_post_thumbnail($post_id, 'medium');
        
        switch ($template) {
            case 'card':
                echo '<div class="pgs-post-card">';
                echo '<div class="pgs-post-thumbnail">' . $thumbnail . '</div>';
                echo '<div class="pgs-post-content">';
                echo '<h3 class="pgs-post-title">' . esc_html($title) . '</h3>';
                echo '<p class="pgs-post-excerpt">' . esc_html($excerpt) . '</p>';
                echo '<div class="pgs-post-meta">';
                echo '<span class="pgs-post-author">By ' . esc_html($author) . '</span>';
                echo '<span class="pgs-post-date">' . esc_html($date) . '</span>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
                break;
                
            case 'list':
                echo '<div class="pgs-post-list">';
                echo '<div class="pgs-post-thumbnail">' . $thumbnail . '</div>';
                echo '<div class="pgs-post-content">';
                echo '<h3 class="pgs-post-title">' . esc_html($title) . '</h3>';
                echo '<p class="pgs-post-excerpt">' . esc_html($excerpt) . '</p>';
                echo '<div class="pgs-post-meta">';
                echo '<span class="pgs-post-author">By ' . esc_html($author) . '</span>';
                echo '<span class="pgs-post-date">' . esc_html($date) . '</span>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
                break;
                
            case 'minimal':
                echo '<div class="pgs-post-minimal">';
                echo '<h3 class="pgs-post-title">' . esc_html($title) . '</h3>';
                echo '<div class="pgs-post-meta">';
                echo '<span class="pgs-post-author">By ' . esc_html($author) . '</span>';
                echo '<span class="pgs-post-date">' . esc_html($date) . '</span>';
                echo '</div>';
                echo '</div>';
                break;
        }
    }
}

// Initialize the plugin
new PostsGridSearchPlugin();

// Include widget classes
require_once PGS_PLUGIN_PATH . 'includes/class-posts-grid-widget.php';
require_once PGS_PLUGIN_PATH . 'includes/class-search-filter-widget.php';