<?php
/**
 * Advanced Filters and Pagination Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class PGS_Advanced_Filters {
    
    public function __construct() {
        add_action('wp_ajax_pgs_filter_posts', array($this, 'ajax_filter_posts'));
        add_action('wp_ajax_nopriv_pgs_filter_posts', array($this, 'ajax_filter_posts'));
        add_action('wp_ajax_pgs_acf_filter_posts', array($this, 'ajax_acf_filter_posts'));
        add_action('wp_ajax_nopriv_pgs_acf_filter_posts', array($this, 'ajax_acf_filter_posts'));
    }
    
    public function ajax_filter_posts() {
        check_ajax_referer('pgs_nonce', 'nonce');
        
        $search_query = sanitize_text_field($_POST['search_query'] ?? '');
        $posts_per_page = intval($_POST['posts_per_page'] ?? 6);
        $template = sanitize_text_field($_POST['template'] ?? 'card');
        $page = intval($_POST['page'] ?? 1);
        
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
        
        // Generate pagination HTML
        $pagination_html = '';
        if ($query->max_num_pages > 1) {
            $pagination_html = $this->generate_ajax_pagination($query->max_num_pages, $page);
        }
        
        wp_send_json_success(array(
            'posts' => $posts_html,
            'pagination' => $pagination_html,
            'total_pages' => $query->max_num_pages,
            'current_page' => $page,
            'total_posts' => $query->found_posts
        ));
    }
    
    public function ajax_acf_filter_posts() {
        check_ajax_referer('pgs_nonce', 'nonce');
        
        $posts_per_page = intval($_POST['posts_per_page'] ?? 6);
        $page = intval($_POST['page'] ?? 1);
        $filters = $_POST['filters'] ?? array();
        
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $posts_per_page,
            'paged' => $page,
            'meta_query' => array('relation' => 'AND')
        );
        
        // Add ACF field filters
        foreach ($filters as $field_key => $field_value) {
            if (!empty($field_value) && strpos($field_key, 'acf_') === 0) {
                $acf_key = str_replace('acf_', '', $field_key);
                $args['meta_query'][] = array(
                    'key' => $acf_key,
                    'value' => sanitize_text_field($field_value),
                    'compare' => '='
                );
            }
        }
        
        // Add date filters
        if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
            $date_query = array();
            
            if (!empty($filters['date_from'])) {
                $date_query['after'] = sanitize_text_field($filters['date_from']);
            }
            
            if (!empty($filters['date_to'])) {
                $date_query['before'] = sanitize_text_field($filters['date_to']);
            }
            
            if (!empty($date_query)) {
                $args['date_query'] = array($date_query);
            }
        }
        
        $query = new WP_Query($args);
        
        ob_start();
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $this->render_acf_filtered_post();
            }
        } else {
            echo '<div class="pgs-no-posts">' . __('No posts found matching the selected filters.', 'posts-grid-search') . '</div>';
        }
        wp_reset_postdata();
        
        $posts_html = ob_get_clean();
        
        // Generate pagination HTML for ACF filters
        $pagination_html = '';
        if ($query->max_num_pages > 1) {
            $pagination_html = $this->generate_acf_pagination($query->max_num_pages, $page, $filters);
        }
        
        wp_send_json_success(array(
            'posts' => $posts_html,
            'pagination' => $pagination_html,
            'total_pages' => $query->max_num_pages,
            'current_page' => $page,
            'total_posts' => $query->found_posts
        ));
    }
    
    private function render_post_template($template) {
        $post_id = get_the_ID();
        $title = get_the_title();
        $excerpt = get_the_excerpt();
        $author = get_the_author();
        $date = get_the_date();
        $thumbnail = get_the_post_thumbnail($post_id, 'medium');
        $permalink = get_permalink();
        
        // Check if template is an Elementor template
        if (is_numeric($template) && $this->is_elementor_template($template)) {
            echo '<article class="pgs-post-elementor">';
            echo '<a href="' . esc_url($permalink) . '" class="pgs-post-link">';
            
            if (class_exists('\Elementor\Plugin')) {
                $elementor_content = \Elementor\Plugin::instance()->frontend->get_builder_content($template);
                
                // Replace dynamic content placeholders
                $elementor_content = str_replace('{{post_title}}', esc_html($title), $elementor_content);
                $elementor_content = str_replace('{{post_excerpt}}', esc_html($excerpt), $elementor_content);
                $elementor_content = str_replace('{{post_author}}', esc_html($author), $elementor_content);
                $elementor_content = str_replace('{{post_date}}', esc_html($date), $elementor_content);
                $elementor_content = str_replace('{{post_url}}', esc_url($permalink), $elementor_content);
                
                echo $elementor_content;
            }
            
            echo '</a>';
            echo '</article>';
        } else {
            // Default templates
            switch ($template) {
                case 'card':
                    echo '<article class="pgs-post-card">';
                    echo '<a href="' . esc_url($permalink) . '" class="pgs-post-link">';
                    if ($thumbnail) {
                        echo '<div class="pgs-post-thumbnail">' . $thumbnail . '</div>';
                    }
                    echo '<div class="pgs-post-content">';
                    echo '<h3 class="pgs-post-title">' . esc_html($title) . '</h3>';
                    echo '<p class="pgs-post-excerpt">' . esc_html($excerpt) . '</p>';
                    echo '<div class="pgs-post-meta">';
                    echo '<span class="pgs-post-author">By ' . esc_html($author) . '</span>';
                    echo '<span class="pgs-post-date">' . esc_html($date) . '</span>';
                    echo '</div>';
                    echo '</div>';
                    echo '</a>';
                    echo '</article>';
                    break;
                    
                case 'list':
                    echo '<article class="pgs-post-list">';
                    echo '<a href="' . esc_url($permalink) . '" class="pgs-post-link">';
                    if ($thumbnail) {
                        echo '<div class="pgs-post-thumbnail">' . $thumbnail . '</div>';
                    }
                    echo '<div class="pgs-post-content">';
                    echo '<h3 class="pgs-post-title">' . esc_html($title) . '</h3>';
                    echo '<p class="pgs-post-excerpt">' . esc_html($excerpt) . '</p>';
                    echo '<div class="pgs-post-meta">';
                    echo '<span class="pgs-post-author">By ' . esc_html($author) . '</span>';
                    echo '<span class="pgs-post-date">' . esc_html($date) . '</span>';
                    echo '</div>';
                    echo '</div>';
                    echo '</a>';
                    echo '</article>';
                    break;
                    
                case 'minimal':
                    echo '<article class="pgs-post-minimal">';
                    echo '<a href="' . esc_url($permalink) . '" class="pgs-post-link">';
                    echo '<h3 class="pgs-post-title">' . esc_html($title) . '</h3>';
                    echo '<div class="pgs-post-meta">';
                    echo '<span class="pgs-post-author">By ' . esc_html($author) . '</span>';
                    echo '<span class="pgs-post-date">' . esc_html($date) . '</span>';
                    echo '</div>';
                    echo '</a>';
                    echo '</article>';
                    break;
            }
        }
    }
    
    private function render_acf_filtered_post() {
        $post_id = get_the_ID();
        $title = get_the_title();
        $excerpt = get_the_excerpt();
        $author = get_the_author();
        $date = get_the_date();
        $thumbnail = get_the_post_thumbnail($post_id, 'medium');
        $permalink = get_permalink();
        
        echo '<article class="pgs-filtered-post">';
        echo '<a href="' . esc_url($permalink) . '" class="pgs-post-link">';
        if ($thumbnail) {
            echo '<div class="pgs-post-thumbnail">' . $thumbnail . '</div>';
        }
        echo '<div class="pgs-post-content">';
        echo '<h3 class="pgs-post-title">' . esc_html($title) . '</h3>';
        echo '<p class="pgs-post-excerpt">' . esc_html($excerpt) . '</p>';
        echo '<div class="pgs-post-meta">';
        echo '<span class="pgs-post-author">By ' . esc_html($author) . '</span>';
        echo '<span class="pgs-post-date">' . esc_html($date) . '</span>';
        echo '</div>';
        echo '</div>';
        echo '</a>';
        echo '</article>';
    }
    
    private function is_elementor_template($template_id) {
        if (!class_exists('\Elementor\Plugin')) {
            return false;
        }
        
        $post_type = get_post_type($template_id);
        return in_array($post_type, ['elementor_library', 'page', 'post']) && 
               get_post_meta($template_id, '_elementor_edit_mode', true) === 'builder';
    }
    
    private function generate_ajax_pagination($total_pages, $current_page) {
        $pagination_html = '';
        
        // Previous button
        if ($current_page > 1) {
            $pagination_html .= '<a href="#" data-page="' . ($current_page - 1) . '" class="pgs-pagination-btn pgs-pagination-prev">←</a>';
        }
        
        // Page numbers
        for ($i = 1; $i <= $total_pages; $i++) {
            if ($i == $current_page) {
                $pagination_html .= '<span class="pgs-pagination-btn pgs-pagination-current">' . $i . '</span>';
            } else {
                $pagination_html .= '<a href="#" data-page="' . $i . '" class="pgs-pagination-btn">' . $i . '</a>';
            }
        }
        
        // Next button
        if ($current_page < $total_pages) {
            $pagination_html .= '<a href="#" data-page="' . ($current_page + 1) . '" class="pgs-pagination-btn pgs-pagination-next">→</a>';
        }
        
        return $pagination_html;
    }
    
    private function generate_acf_pagination($total_pages, $current_page, $filters) {
        $pagination_html = '';
        
        // Previous button
        if ($current_page > 1) {
            $pagination_html .= '<a href="#" data-page="' . ($current_page - 1) . '" class="pgs-acf-pagination-btn pgs-pagination-prev" data-filters="' . esc_attr(json_encode($filters)) . '">←</a>';
        }
        
        // Page numbers
        for ($i = 1; $i <= $total_pages; $i++) {
            if ($i == $current_page) {
                $pagination_html .= '<span class="pgs-acf-pagination-btn pgs-pagination-current">' . $i . '</span>';
            } else {
                $pagination_html .= '<a href="#" data-page="' . $i . '" class="pgs-acf-pagination-btn" data-filters="' . esc_attr(json_encode($filters)) . '">' . $i . '</a>';
            }
        }
        
        // Next button
        if ($current_page < $total_pages) {
            $pagination_html .= '<a href="#" data-page="' . ($current_page + 1) . '" class="pgs-acf-pagination-btn pgs-pagination-next" data-filters="' . esc_attr(json_encode($filters)) . '">→</a>';
        }
        
        return $pagination_html;
    }
}

// Initialize the filters
new PGS_Advanced_Filters();