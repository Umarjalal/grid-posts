<?php
/**
 * ACF Loop Grid Filters Widget Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class PGS_ACF_Loop_Grid_Filters_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'pgs_acf_loop_grid_filters',
            __('ACF Loop Grid Filters Pro', 'posts-grid-search'),
            array(
                'description' => __('Professional ACF-based post filters with advanced styling and pagination support.', 'posts-grid-search'),
                'classname' => 'pgs-acf-loop-grid-filters-widget'
            )
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        // Generate custom styles
        $custom_styles = $this->generate_custom_styles($instance);
        if ($custom_styles) {
            echo '<style>' . $custom_styles . '</style>';
        }
        
        $this->render_filters($instance);
        
        echo $args['after_widget'];
    }
    
    private function generate_custom_styles($instance) {
        $widget_id = $this->id;
        $styles = '';
        
        // Container styles
        if (!empty($instance['container_bg'])) {
            $styles .= "#{$widget_id} .pgs-acf-filters { background-color: {$instance['container_bg']}; }";
        }
        if (!empty($instance['container_padding'])) {
            $styles .= "#{$widget_id} .pgs-acf-filters { padding: {$instance['container_padding']}px; }";
        }
        if (!empty($instance['container_border_radius'])) {
            $styles .= "#{$widget_id} .pgs-acf-filters { border-radius: {$instance['container_border_radius']}px; }";
        }
        
        // Filter styles
        if (!empty($instance['filter_bg'])) {
            $styles .= "#{$widget_id} .pgs-filter-select, #{$widget_id} .pgs-filter-input { background-color: {$instance['filter_bg']}; }";
        }
        if (!empty($instance['filter_text_color'])) {
            $styles .= "#{$widget_id} .pgs-filter-select, #{$widget_id} .pgs-filter-input, #{$widget_id} .pgs-filter-label { color: {$instance['filter_text_color']}; }";
        }
        if (!empty($instance['filter_border_color'])) {
            $styles .= "#{$widget_id} .pgs-filter-select, #{$widget_id} .pgs-filter-input { border-color: {$instance['filter_border_color']}; }";
        }
        if (!empty($instance['filter_border_radius'])) {
            $styles .= "#{$widget_id} .pgs-filter-select, #{$widget_id} .pgs-filter-input { border-radius: {$instance['filter_border_radius']}px; }";
        }
        
        return $styles;
    }
    
    private function render_filters($instance) {
        $acf_fields = $this->get_acf_filter_fields($instance);
        $show_date_filter = !empty($instance['show_date_filter']) ? true : false;
        
        ?>
        <div class="pgs-acf-filters" data-posts-per-page="<?php echo esc_attr($instance['posts_per_page']); ?>">
            <form class="pgs-filters-form" method="get">
                <div class="pgs-filters-row">
                    
                    <?php if (!empty($acf_fields)) : ?>
                        <?php foreach ($acf_fields as $field_key => $field_label) : ?>
                            <div class="pgs-filter-group">
                                <label class="pgs-filter-label" for="filter_<?php echo esc_attr($field_key); ?>">
                                    <?php echo esc_html($field_label); ?>
                                </label>
                                <?php $this->render_acf_filter($field_key, $instance); ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <?php if ($show_date_filter) : ?>
                        <div class="pgs-filter-group">
                            <label class="pgs-filter-label" for="filter_date_from"><?php _e('Date From:', 'posts-grid-search'); ?></label>
                            <input type="date" id="filter_date_from" name="filter_date_from" class="pgs-filter-input" value="<?php echo esc_attr($_GET['filter_date_from'] ?? ''); ?>">
                        </div>
                        
                        <div class="pgs-filter-group">
                            <label class="pgs-filter-label" for="filter_date_to"><?php _e('Date To:', 'posts-grid-search'); ?></label>
                            <input type="date" id="filter_date_to" name="filter_date_to" class="pgs-filter-input" value="<?php echo esc_attr($_GET['filter_date_to'] ?? ''); ?>">
                        </div>
                    <?php endif; ?>
                    
                    <div class="pgs-filter-actions">
                        <button type="button" class="pgs-filter-clear"><?php _e('Clear', 'posts-grid-search'); ?></button>
                    </div>
                </div>
            </form>
            
            <div class="pgs-filtered-results">
                <?php $this->render_filtered_posts($instance); ?>
            </div>
        </div>
        <?php
    }
    
    private function get_acf_filter_fields($instance) {
        $fields = array();
        
        // Get ACF fields from instance settings
        for ($i = 1; $i <= 5; $i++) {
            $field_key = $instance["acf_field_{$i}_key"] ?? '';
            $field_label = $instance["acf_field_{$i}_label"] ?? '';
            
            if (!empty($field_key) && !empty($field_label)) {
                $fields[$field_key] = $field_label;
            }
        }
        
        return $fields;
    }
    
    private function render_acf_filter($field_key, $instance) {
        // Get unique values for this ACF field
        global $wpdb;
        
        $values = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} 
             WHERE meta_key = %s AND meta_value != '' 
             ORDER BY meta_value ASC",
            $field_key
        ));
        
        if (!empty($values)) {
            echo '<select id="filter_' . esc_attr($field_key) . '" name="filter_' . esc_attr($field_key) . '" class="pgs-filter-select">';
            echo '<option value="">' . __('All', 'posts-grid-search') . '</option>';
            
            $current_value = $_GET["filter_{$field_key}"] ?? '';
            
            foreach ($values as $value) {
                echo '<option value="' . esc_attr($value) . '" ' . selected($current_value, $value, false) . '>';
                echo esc_html($value);
                echo '</option>';
            }
            echo '</select>';
        }
    }
    
    private function render_filtered_posts($instance) {
        $posts_per_page = !empty($instance['posts_per_page']) ? intval($instance['posts_per_page']) : 6;
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        
        // Build query args based on filters
        $query_args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $posts_per_page,
            'paged' => $paged,
            'meta_query' => array('relation' => 'AND')
        );
        
        // Add ACF field filters
        $acf_fields = $this->get_acf_filter_fields($instance);
        foreach ($acf_fields as $field_key => $field_label) {
            $filter_value = $_GET["filter_{$field_key}"] ?? '';
            if (!empty($filter_value)) {
                $query_args['meta_query'][] = array(
                    'key' => $field_key,
                    'value' => $filter_value,
                    'compare' => '='
                );
            }
        }
        
        // Add date filters
        if (!empty($_GET['filter_date_from']) || !empty($_GET['filter_date_to'])) {
            $date_query = array();
            
            if (!empty($_GET['filter_date_from'])) {
                $date_query['after'] = $_GET['filter_date_from'];
            }
            
            if (!empty($_GET['filter_date_to'])) {
                $date_query['before'] = $_GET['filter_date_to'];
            }
            
            if (!empty($date_query)) {
                $query_args['date_query'] = array($date_query);
            }
        }
        
        $posts_query = new WP_Query($query_args);
        
        echo '<div class="pgs-filtered-posts-container">';
        
        if ($posts_query->have_posts()) {
            while ($posts_query->have_posts()) {
                $posts_query->the_post();
                $this->render_filtered_post($instance);
            }
            
            // Render pagination if needed
            if ($posts_query->max_num_pages > 1) {
                $this->render_filter_pagination($posts_query, $instance);
            }
        } else {
            echo '<div class="pgs-no-posts">' . __('No posts found matching the selected filters.', 'posts-grid-search') . '</div>';
        }
        
        echo '</div>';
        
        wp_reset_postdata();
    }
    
    private function render_filtered_post($instance) {
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
    
    private function render_filter_pagination($query, $instance) {
        $current_page = max(1, get_query_var('paged'));
        $total_pages = $query->max_num_pages;
        
        echo '<div class="pgs-filter-pagination">';
        
        // Previous button
        if ($current_page > 1) {
            $prev_link = $this->get_filter_page_link($current_page - 1);
            echo '<a href="' . esc_url($prev_link) . '" class="pgs-pagination-btn">' . __('Previous', 'posts-grid-search') . '</a>';
        }
        
        // Page numbers
        for ($i = 1; $i <= $total_pages; $i++) {
            if ($i == $current_page) {
                echo '<span class="pgs-pagination-btn pgs-pagination-current">' . $i . '</span>';
            } else {
                $page_link = $this->get_filter_page_link($i);
                echo '<a href="' . esc_url($page_link) . '" class="pgs-pagination-btn">' . $i . '</a>';
            }
        }
        
        // Next button
        if ($current_page < $total_pages) {
            $next_link = $this->get_filter_page_link($current_page + 1);
            echo '<a href="' . esc_url($next_link) . '" class="pgs-pagination-btn">' . __('Next', 'posts-grid-search') . '</a>';
        }
        
        echo '</div>';
    }
    
    private function get_filter_page_link($page) {
        $current_url = home_url(add_query_arg(array()));
        $query_args = $_GET;
        $query_args['paged'] = $page;
        
        return add_query_arg($query_args, $current_url);
    }
    
    public function form($instance) {
        // Default values
        $defaults = array(
            'title' => '',
            'posts_per_page' => '6',
            'show_date_filter' => false,
            // ACF field settings
            'acf_field_1_key' => '',
            'acf_field_1_label' => '',
            'acf_field_2_key' => '',
            'acf_field_2_label' => '',
            'acf_field_3_key' => '',
            'acf_field_3_label' => '',
            'acf_field_4_key' => '',
            'acf_field_4_label' => '',
            'acf_field_5_key' => '',
            'acf_field_5_label' => '',
            // Style defaults
            'container_bg' => '',
            'container_padding' => '20',
            'container_border_radius' => '8',
            'filter_bg' => '#ffffff',
            'filter_text_color' => '#1a202c',
            'filter_border_color' => '#14b8a6',
            'filter_border_radius' => '6'
        );
        
        $instance = wp_parse_args((array) $instance, $defaults);
        ?>
        <div class="pgs-widget-form">
            <!-- Widget Tabs -->
            <div class="pgs-widget-tabs">
                <div class="pgs-tab-nav">
                    <button type="button" class="pgs-tab-btn pgs-tab-active" data-tab="content"><?php _e('Content', 'posts-grid-search'); ?></button>
                    <button type="button" class="pgs-tab-btn" data-tab="style"><?php _e('Style', 'posts-grid-search'); ?></button>
                </div>
                
                <!-- Content Tab -->
                <div class="pgs-tab-content pgs-tab-active" data-tab="content">
                    <p>
                        <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title:', 'posts-grid-search'); ?></label>
                        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>">
                    </p>
                    
                    <p>
                        <label for="<?php echo $this->get_field_id('posts_per_page'); ?>"><?php _e('Posts per page:', 'posts-grid-search'); ?></label>
                        <input class="widefat" id="<?php echo $this->get_field_id('posts_per_page'); ?>" name="<?php echo $this->get_field_name('posts_per_page'); ?>" type="number" value="<?php echo esc_attr($instance['posts_per_page']); ?>" min="1">
                    </p>
                    
                    <h4><?php _e('ACF Filter Fields', 'posts-grid-search'); ?></h4>
                    
                    <?php for ($i = 1; $i <= 5; $i++) : ?>
                        <div class="pgs-settings-section">
                            <h4><?php printf(__('ACF Field %d', 'posts-grid-search'), $i); ?></h4>
                            
                            <p>
                                <label for="<?php echo $this->get_field_id("acf_field_{$i}_key"); ?>"><?php _e('Field Key:', 'posts-grid-search'); ?></label>
                                <input class="widefat" id="<?php echo $this->get_field_id("acf_field_{$i}_key"); ?>" name="<?php echo $this->get_field_name("acf_field_{$i}_key"); ?>" type="text" value="<?php echo esc_attr($instance["acf_field_{$i}_key"]); ?>">
                                <small><?php _e('ACF field name (e.g., category, location)', 'posts-grid-search'); ?></small>
                            </p>
                            
                            <p>
                                <label for="<?php echo $this->get_field_id("acf_field_{$i}_label"); ?>"><?php _e('Filter Label:', 'posts-grid-search'); ?></label>
                                <input class="widefat" id="<?php echo $this->get_field_id("acf_field_{$i}_label"); ?>" name="<?php echo $this->get_field_name("acf_field_{$i}_label"); ?>" type="text" value="<?php echo esc_attr($instance["acf_field_{$i}_label"]); ?>">
                                <small><?php _e('Display label for the filter dropdown', 'posts-grid-search'); ?></small>
                            </p>
                        </div>
                    <?php endfor; ?>
                    
                    <h4><?php _e('Date Filter', 'posts-grid-search'); ?></h4>
                    
                    <p>
                        <input class="checkbox" type="checkbox" <?php checked($instance['show_date_filter'], true); ?> id="<?php echo $this->get_field_id('show_date_filter'); ?>" name="<?php echo $this->get_field_name('show_date_filter'); ?>">
                        <label for="<?php echo $this->get_field_id('show_date_filter'); ?>"><?php _e('Show date range filter', 'posts-grid-search'); ?></label>
                    </p>
                </div>
                
                <!-- Style Tab -->
                <div class="pgs-tab-content" data-tab="style">
                    <h4><?php _e('Container Styling', 'posts-grid-search'); ?></h4>
                    
                    <p>
                        <label for="<?php echo $this->get_field_id('container_bg'); ?>"><?php _e('Container Background:', 'posts-grid-search'); ?></label>
                        <input class="widefat" id="<?php echo $this->get_field_id('container_bg'); ?>" name="<?php echo $this->get_field_name('container_bg'); ?>" type="color" value="<?php echo esc_attr($instance['container_bg']); ?>">
                    </p>
                    
                    <p>
                        <label for="<?php echo $this->get_field_id('container_padding'); ?>"><?php _e('Container Padding (px):', 'posts-grid-search'); ?></label>
                        <input class="widefat" id="<?php echo $this->get_field_id('container_padding'); ?>" name="<?php echo $this->get_field_name('container_padding'); ?>" type="number" value="<?php echo esc_attr($instance['container_padding']); ?>" min="0">
                    </p>
                    
                    <p>
                        <label for="<?php echo $this->get_field_id('container_border_radius'); ?>"><?php _e('Container Border Radius (px):', 'posts-grid-search'); ?></label>
                        <input class="widefat" id="<?php echo $this->get_field_id('container_border_radius'); ?>" name="<?php echo $this->get_field_name('container_border_radius'); ?>" type="number" value="<?php echo esc_attr($instance['container_border_radius']); ?>" min="0">
                    </p>
                    
                    <h4><?php _e('Filter Styling', 'posts-grid-search'); ?></h4>
                    
                    <p>
                        <label for="<?php echo $this->get_field_id('filter_bg'); ?>"><?php _e('Filter Background:', 'posts-grid-search'); ?></label>
                        <input class="widefat" id="<?php echo $this->get_field_id('filter_bg'); ?>" name="<?php echo $this->get_field_name('filter_bg'); ?>" type="color" value="<?php echo esc_attr($instance['filter_bg']); ?>">
                    </p>
                    
                    <p>
                        <label for="<?php echo $this->get_field_id('filter_text_color'); ?>"><?php _e('Filter Text Color:', 'posts-grid-search'); ?></label>
                        <input class="widefat" id="<?php echo $this->get_field_id('filter_text_color'); ?>" name="<?php echo $this->get_field_name('filter_text_color'); ?>" type="color" value="<?php echo esc_attr($instance['filter_text_color']); ?>">
                    </p>
                    
                    <p>
                        <label for="<?php echo $this->get_field_id('filter_border_color'); ?>"><?php _e('Filter Border Color:', 'posts-grid-search'); ?></label>
                        <input class="widefat" id="<?php echo $this->get_field_id('filter_border_color'); ?>" name="<?php echo $this->get_field_name('filter_border_color'); ?>" type="color" value="<?php echo esc_attr($instance['filter_border_color']); ?>">
                    </p>
                    
                    <p>
                        <label for="<?php echo $this->get_field_id('filter_border_radius'); ?>"><?php _e('Filter Border Radius (px):', 'posts-grid-search'); ?></label>
                        <input class="widefat" id="<?php echo $this->get_field_id('filter_border_radius'); ?>" name="<?php echo $this->get_field_name('filter_border_radius'); ?>" type="number" value="<?php echo esc_attr($instance['filter_border_radius']); ?>" min="0">
                    </p>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        
        // Content settings
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['posts_per_page'] = (!empty($new_instance['posts_per_page'])) ? intval($new_instance['posts_per_page']) : 6;
        $instance['show_date_filter'] = !empty($new_instance['show_date_filter']) ? 1 : 0;
        
        // ACF field settings
        for ($i = 1; $i <= 5; $i++) {
            $instance["acf_field_{$i}_key"] = (!empty($new_instance["acf_field_{$i}_key"])) ? sanitize_text_field($new_instance["acf_field_{$i}_key"]) : '';
            $instance["acf_field_{$i}_label"] = (!empty($new_instance["acf_field_{$i}_label"])) ? sanitize_text_field($new_instance["acf_field_{$i}_label"]) : '';
        }
        
        // Style settings
        $instance['container_bg'] = (!empty($new_instance['container_bg'])) ? sanitize_hex_color($new_instance['container_bg']) : '';
        $instance['container_padding'] = (!empty($new_instance['container_padding'])) ? intval($new_instance['container_padding']) : 20;
        $instance['container_border_radius'] = (!empty($new_instance['container_border_radius'])) ? intval($new_instance['container_border_radius']) : 8;
        $instance['filter_bg'] = (!empty($new_instance['filter_bg'])) ? sanitize_hex_color($new_instance['filter_bg']) : '#ffffff';
        $instance['filter_text_color'] = (!empty($new_instance['filter_text_color'])) ? sanitize_hex_color($new_instance['filter_text_color']) : '#1a202c';
        $instance['filter_border_color'] = (!empty($new_instance['filter_border_color'])) ? sanitize_hex_color($new_instance['filter_border_color']) : '#14b8a6';
        $instance['filter_border_radius'] = (!empty($new_instance['filter_border_radius'])) ? intval($new_instance['filter_border_radius']) : 6;
        
        return $instance;
    }
}