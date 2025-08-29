<?php
/**
 * Posts Grid Widget Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class PGS_Posts_Grid_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'pgs_posts_grid',
            __('Posts Grid Pro', 'posts-grid-search'),
            array(
                'description' => __('Professional posts grid widget with Elementor templates integration and advanced styling options.', 'posts-grid-search'),
                'classname' => 'pgs-posts-grid-widget'
            )
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        $template = !empty($instance['template']) ? $instance['template'] : 'card';
        $columns = !empty($instance['columns']) ? intval($instance['columns']) : 3;
        $posts_per_page = !empty($instance['posts_per_page']) ? intval($instance['posts_per_page']) : 6;
        $show_pagination = !empty($instance['show_pagination']) ? true : false;
        $pagination_style = !empty($instance['pagination_style']) ? $instance['pagination_style'] : 'numbers';
        
        // Get current page
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        
        $query_args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $posts_per_page,
            'paged' => $paged
        );
        
        $posts_query = new WP_Query($query_args);
        
        // Generate custom styles
        $custom_styles = $this->generate_custom_styles($instance);
        if ($custom_styles) {
            echo '<style>' . $custom_styles . '</style>';
        }
        
        echo '<div class="pgs-posts-grid" data-template="' . esc_attr($template) . '" data-columns="' . esc_attr($columns) . '" data-posts-per-page="' . esc_attr($posts_per_page) . '">';
        echo '<div class="pgs-posts-container pgs-template-' . esc_attr($template) . ' pgs-columns-' . esc_attr($columns) . '">';
        
        if ($posts_query->have_posts()) {
            while ($posts_query->have_posts()) {
                $posts_query->the_post();
                $this->render_post($template, $instance);
            }
        } else {
            echo '<div class="pgs-no-posts">' . __('No posts found.', 'posts-grid-search') . '</div>';
        }
        
        echo '</div>'; // .pgs-posts-container
        
        // Pagination
        if ($show_pagination && $posts_query->max_num_pages > 1) {
            $this->render_pagination($posts_query, $instance);
        }
        
        echo '</div>'; // .pgs-posts-grid
        
        wp_reset_postdata();
        echo $args['after_widget'];
    }
    
    private function generate_custom_styles($instance) {
        $widget_id = $this->id;
        $styles = '';
        
        // Container styles
        if (!empty($instance['container_bg'])) {
            $styles .= "#{$widget_id} .pgs-posts-container { background-color: {$instance['container_bg']}; }";
        }
        if (!empty($instance['container_padding'])) {
            $styles .= "#{$widget_id} .pgs-posts-container { padding: {$instance['container_padding']}px; }";
        }
        if (!empty($instance['container_margin'])) {
            $styles .= "#{$widget_id} .pgs-posts-container { margin: {$instance['container_margin']}px; }";
        }
        if (!empty($instance['container_border_radius'])) {
            $styles .= "#{$widget_id} .pgs-posts-container { border-radius: {$instance['container_border_radius']}px; }";
        }
        
        // Card styles
        if (!empty($instance['card_bg'])) {
            $styles .= "#{$widget_id} .pgs-post-card, #{$widget_id} .pgs-post-list { background-color: {$instance['card_bg']}; }";
        }
        if (!empty($instance['card_padding'])) {
            $styles .= "#{$widget_id} .pgs-post-content { padding: {$instance['card_padding']}px; }";
        }
        if (!empty($instance['card_border_radius'])) {
            $styles .= "#{$widget_id} .pgs-post-card, #{$widget_id} .pgs-post-list { border-radius: {$instance['card_border_radius']}px; }";
        }
        if (!empty($instance['card_shadow'])) {
            $styles .= "#{$widget_id} .pgs-post-card, #{$widget_id} .pgs-post-list { box-shadow: {$instance['card_shadow']}; }";
        }
        
        // Typography styles
        if (!empty($instance['title_color'])) {
            $styles .= "#{$widget_id} .pgs-post-title { color: {$instance['title_color']}; }";
        }
        if (!empty($instance['title_font_size'])) {
            $styles .= "#{$widget_id} .pgs-post-title { font-size: {$instance['title_font_size']}px; }";
        }
        if (!empty($instance['excerpt_color'])) {
            $styles .= "#{$widget_id} .pgs-post-excerpt { color: {$instance['excerpt_color']}; }";
        }
        if (!empty($instance['excerpt_font_size'])) {
            $styles .= "#{$widget_id} .pgs-post-excerpt { font-size: {$instance['excerpt_font_size']}px; }";
        }
        if (!empty($instance['meta_color'])) {
            $styles .= "#{$widget_id} .pgs-post-meta { color: {$instance['meta_color']}; }";
        }
        
        // Hover effects
        if (!empty($instance['hover_transform'])) {
            $styles .= "#{$widget_id} .pgs-post-card:hover, #{$widget_id} .pgs-post-list:hover { transform: {$instance['hover_transform']}; }";
        }
        
        return $styles;
    }
    
    private function render_post($template, $instance) {
        $post_id = get_the_ID();
        $title = get_the_title();
        $excerpt = get_the_excerpt();
        $author = get_the_author();
        $date = get_the_date();
        $thumbnail = get_the_post_thumbnail($post_id, 'medium');
        $permalink = get_permalink();
        
        $show_excerpt = !empty($instance['show_excerpt']) ? true : false;
        $show_author = !empty($instance['show_author']) ? true : false;
        $show_date = !empty($instance['show_date']) ? true : false;
        
        // Check if template is an Elementor saved template
        if (is_numeric($template) && $this->is_elementor_template($template)) {
            echo '<article class="pgs-post-elementor">';
            echo '<a href="' . esc_url($permalink) . '" class="pgs-post-link">';
            
            // Render Elementor template with post data
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
                    if ($show_excerpt) {
                        echo '<p class="pgs-post-excerpt">' . esc_html($excerpt) . '</p>';
                    }
                    if ($show_author || $show_date) {
                        echo '<div class="pgs-post-meta">';
                        if ($show_author) {
                            echo '<span class="pgs-post-author">By ' . esc_html($author) . '</span>';
                        }
                        if ($show_date) {
                            echo '<span class="pgs-post-date">' . esc_html($date) . '</span>';
                        }
                        echo '</div>';
                    }
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
                    if ($show_excerpt) {
                        echo '<p class="pgs-post-excerpt">' . esc_html($excerpt) . '</p>';
                    }
                    if ($show_author || $show_date) {
                        echo '<div class="pgs-post-meta">';
                        if ($show_author) {
                            echo '<span class="pgs-post-author">By ' . esc_html($author) . '</span>';
                        }
                        if ($show_date) {
                            echo '<span class="pgs-post-date">' . esc_html($date) . '</span>';
                        }
                        echo '</div>';
                    }
                    echo '</div>';
                    echo '</a>';
                    echo '</article>';
                    break;
                    
                case 'minimal':
                    echo '<article class="pgs-post-minimal">';
                    echo '<a href="' . esc_url($permalink) . '" class="pgs-post-link">';
                    echo '<h3 class="pgs-post-title">' . esc_html($title) . '</h3>';
                    if ($show_author || $show_date) {
                        echo '<div class="pgs-post-meta">';
                        if ($show_author) {
                            echo '<span class="pgs-post-author">By ' . esc_html($author) . '</span>';
                        }
                        if ($show_date) {
                            echo '<span class="pgs-post-date">' . esc_html($date) . '</span>';
                        }
                        echo '</div>';
                    }
                    echo '</a>';
                    echo '</article>';
                    break;
            }
        }
    }
    
    private function is_elementor_template($template_id) {
        if (!class_exists('\Elementor\Plugin')) {
            return false;
        }
        
        $post_type = get_post_type($template_id);
        return in_array($post_type, ['elementor_library', 'page', 'post']) && 
               get_post_meta($template_id, '_elementor_edit_mode', true) === 'builder';
    }
    
    private function render_pagination($query, $instance) {
        $current_page = max(1, get_query_var('paged'));
        $total_pages = $query->max_num_pages;
        $pagination_style = !empty($instance['pagination_style']) ? $instance['pagination_style'] : 'numbers';
        $prev_icon = !empty($instance['prev_icon']) ? $instance['prev_icon'] : '←';
        $next_icon = !empty($instance['next_icon']) ? $instance['next_icon'] : '→';
        
        // Pagination colors from style settings
        $pagination_bg = !empty($instance['pagination_bg']) ? $instance['pagination_bg'] : '#1a202c';
        $pagination_active_color = !empty($instance['pagination_active_color']) ? $instance['pagination_active_color'] : '#14b8a6';
        $pagination_text_color = !empty($instance['pagination_text_color']) ? $instance['pagination_text_color'] : '#ffffff';
        
        echo '<div class="pgs-pagination" style="--pagination-bg: ' . esc_attr($pagination_bg) . '; --pagination-active: ' . esc_attr($pagination_active_color) . '; --pagination-text: ' . esc_attr($pagination_text_color) . ';">';
        
        if ($pagination_style === 'numbers') {
            // Previous button
            if ($current_page > 1) {
                $prev_link = get_pagenum_link($current_page - 1);
                echo '<a href="' . esc_url($prev_link) . '" class="pgs-pagination-btn pgs-pagination-prev">' . esc_html($prev_icon) . '</a>';
            }
            
            // Page numbers
            for ($i = 1; $i <= $total_pages; $i++) {
                if ($i == $current_page) {
                    echo '<span class="pgs-pagination-btn pgs-pagination-current">' . $i . '</span>';
                } else {
                    $page_link = get_pagenum_link($i);
                    echo '<a href="' . esc_url($page_link) . '" class="pgs-pagination-btn">' . $i . '</a>';
                }
            }
            
            // Next button
            if ($current_page < $total_pages) {
                $next_link = get_pagenum_link($current_page + 1);
                echo '<a href="' . esc_url($next_link) . '" class="pgs-pagination-btn pgs-pagination-next">' . esc_html($next_icon) . '</a>';
            }
            
        } elseif ($pagination_style === 'simple') {
            echo '<div class="pgs-pagination-simple">';
            if ($current_page > 1) {
                $prev_link = get_pagenum_link($current_page - 1);
                echo '<a href="' . esc_url($prev_link) . '" class="pgs-pagination-btn">' . __('Previous', 'posts-grid-search') . '</a>';
            }
            echo '<span class="pgs-pagination-info">' . sprintf(__('Page %d of %d', 'posts-grid-search'), $current_page, $total_pages) . '</span>';
            if ($current_page < $total_pages) {
                $next_link = get_pagenum_link($current_page + 1);
                echo '<a href="' . esc_url($next_link) . '" class="pgs-pagination-btn">' . __('Next', 'posts-grid-search') . '</a>';
            }
            echo '</div>';
            
        } elseif ($pagination_style === 'arrows') {
            if ($current_page > 1) {
                $prev_link = get_pagenum_link($current_page - 1);
                echo '<a href="' . esc_url($prev_link) . '" class="pgs-pagination-btn pgs-pagination-arrow">' . esc_html($prev_icon) . '</a>';
            }
            if ($current_page < $total_pages) {
                $next_link = get_pagenum_link($current_page + 1);
                echo '<a href="' . esc_url($next_link) . '" class="pgs-pagination-btn pgs-pagination-arrow">' . esc_html($next_icon) . '</a>';
            }
        }
        
        echo '</div>';
    }
    
    private function get_elementor_templates() {
        if (!class_exists('\Elementor\Plugin')) {
            return array();
        }
        
        $templates = get_posts(array(
            'post_type' => 'elementor_library',
            'post_status' => 'publish',
            'numberposts' => -1,
            'meta_query' => array(
                array(
                    'key' => '_elementor_template_type',
                    'value' => array('page', 'section', 'container'),
                    'compare' => 'IN'
                )
            )
        ));
        
        return $templates;
    }
    
    public function form($instance) {
        // Default values
        $defaults = array(
            'title' => '',
            'template' => 'card',
            'columns' => '3',
            'posts_per_page' => '6',
            'show_pagination' => false,
            'pagination_style' => 'numbers',
            'prev_icon' => '←',
            'next_icon' => '→',
            'show_excerpt' => true,
            'show_author' => true,
            'show_date' => true,
            // Style defaults
            'container_bg' => '',
            'container_padding' => '20',
            'container_margin' => '0',
            'container_border_radius' => '8',
            'card_bg' => '#ffffff',
            'card_padding' => '20',
            'card_border_radius' => '8',
            'card_shadow' => '0 2px 10px rgba(0, 0, 0, 0.1)',
            'title_color' => '#1a202c',
            'title_font_size' => '18',
            'excerpt_color' => '#4a5568',
            'excerpt_font_size' => '14',
            'meta_color' => '#718096',
            'hover_transform' => 'translateY(-5px)',
            'pagination_bg' => '#1a202c',
            'pagination_active_color' => '#14b8a6',
            'pagination_text_color' => '#ffffff'
        );
        
        $instance = wp_parse_args((array) $instance, $defaults);
        
        // Get Elementor templates
        $elementor_templates = $this->get_elementor_templates();
        ?>
        <div class="pgs-widget-form">
            <!-- Content Tab -->
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
                    
                    <h4><?php _e('Layout Settings', 'posts-grid-search'); ?></h4>
                    
                    <p>
                        <label for="<?php echo $this->get_field_id('template'); ?>"><?php _e('Template:', 'posts-grid-search'); ?></label>
                        <select class="widefat" id="<?php echo $this->get_field_id('template'); ?>" name="<?php echo $this->get_field_name('template'); ?>">
                            <optgroup label="<?php _e('Built-in Templates', 'posts-grid-search'); ?>">
                                <option value="card" <?php selected($instance['template'], 'card'); ?>><?php _e('Card', 'posts-grid-search'); ?></option>
                                <option value="list" <?php selected($instance['template'], 'list'); ?>><?php _e('List', 'posts-grid-search'); ?></option>
                                <option value="minimal" <?php selected($instance['template'], 'minimal'); ?>><?php _e('Minimal', 'posts-grid-search'); ?></option>
                            </optgroup>
                            <?php if (!empty($elementor_templates)) : ?>
                                <optgroup label="<?php _e('Elementor Templates', 'posts-grid-search'); ?>">
                                    <?php foreach ($elementor_templates as $template) : ?>
                                        <option value="<?php echo esc_attr($template->ID); ?>" <?php selected($instance['template'], $template->ID); ?>>
                                            <?php echo esc_html($template->post_title); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endif; ?>
                        </select>
                        <small><?php _e('Select from built-in templates or your saved Elementor templates.', 'posts-grid-search'); ?></small>
                    </p>
                    
                    <p>
                        <label for="<?php echo $this->get_field_id('columns'); ?>"><?php _e('Columns:', 'posts-grid-search'); ?></label>
                        <select class="widefat" id="<?php echo $this->get_field_id('columns'); ?>" name="<?php echo $this->get_field_name('columns'); ?>">
                            <option value="1" <?php selected($instance['columns'], '1'); ?>>1</option>
                            <option value="2" <?php selected($instance['columns'], '2'); ?>>2</option>
                            <option value="3" <?php selected($instance['columns'], '3'); ?>>3</option>
                            <option value="4" <?php selected($instance['columns'], '4'); ?>>4</option>
                        </select>
                    </p>
                    
                    <p>
                        <label for="<?php echo $this->get_field_id('posts_per_page'); ?>"><?php _e('Posts per page:', 'posts-grid-search'); ?></label>
                        <input class="widefat" id="<?php echo $this->get_field_id('posts_per_page'); ?>" name="<?php echo $this->get_field_name('posts_per_page'); ?>" type="number" value="<?php echo esc_attr($instance['posts_per_page']); ?>" min="1">
                    </p>
                    
                    <h4><?php _e('Content Display', 'posts-grid-search'); ?></h4>
                    
                    <p>
                        <input class="checkbox" type="checkbox" <?php checked($instance['show_excerpt'], true); ?> id="<?php echo $this->get_field_id('show_excerpt'); ?>" name="<?php echo $this->get_field_name('show_excerpt'); ?>">
                        <label for="<?php echo $this->get_field_id('show_excerpt'); ?>"><?php _e('Show excerpt', 'posts-grid-search'); ?></label>
                    </p>
                    
                    <p>
                        <input class="checkbox" type="checkbox" <?php checked($instance['show_author'], true); ?> id="<?php echo $this->get_field_id('show_author'); ?>" name="<?php echo $this->get_field_name('show_author'); ?>">
                        <label for="<?php echo $this->get_field_id('show_author'); ?>"><?php _e('Show author', 'posts-grid-search'); ?></label>
                    </p>
                    
                    <p>
                        <input class="checkbox" type="checkbox" <?php checked($instance['show_date'], true); ?> id="<?php echo $this->get_field_id('show_date'); ?>" name="<?php echo $this->get_field_name('show_date'); ?>">
                        <label for="<?php echo $this->get_field_id('show_date'); ?>"><?php _e('Show date', 'posts-grid-search'); ?></label>
                    </p>
                    
                    <h4><?php _e('Pagination Settings', 'posts-grid-search'); ?></h4>
                    
                    <p>
                        <input class="checkbox" type="checkbox" <?php checked($instance['show_pagination'], true); ?> id="<?php echo $this->get_field_id('show_pagination'); ?>" name="<?php echo $this->get_field_name('show_pagination'); ?>">
                        <label for="<?php echo $this->get_field_id('show_pagination'); ?>"><?php _e('Show pagination', 'posts-grid-search'); ?></label>
                    </p>
                    
                    <p>
                        <label for="<?php echo $this->get_field_id('pagination_style'); ?>"><?php _e('Pagination style:', 'posts-grid-search'); ?></label>
                        <select class="widefat" id="<?php echo $this->get_field_id('pagination_style'); ?>" name="<?php echo $this->get_field_name('pagination_style'); ?>">
                            <option value="numbers" <?php selected($instance['pagination_style'], 'numbers'); ?>><?php _e('Numbers', 'posts-grid-search'); ?></option>
                            <option value="simple" <?php selected($instance['pagination_style'], 'simple'); ?>><?php _e('Simple', 'posts-grid-search'); ?></option>
                            <option value="arrows" <?php selected($instance['pagination_style'], 'arrows'); ?>><?php _e('Arrows only', 'posts-grid-search'); ?></option>
                        </select>
                    </p>
                    
                    <p>
                        <label for="<?php echo $this->get_field_id('prev_icon'); ?>"><?php _e('Previous icon:', 'posts-grid-search'); ?></label>
                        <input class="widefat" id="<?php echo $this->get_field_id('prev_icon'); ?>" name="<?php echo $this->get_field_name('prev_icon'); ?>" type="text" value="<?php echo esc_attr($instance['prev_icon']); ?>">
                    </p>
                    
                    <p>
                        <label for="<?php echo $this->get_field_id('next_icon'); ?>"><?php _e('Next icon:', 'posts-grid-search'); ?></label>
                        <input class="widefat" id="<?php echo $this->get_field_id('next_icon'); ?>" name="<?php echo $this->get_field_name('next_icon'); ?>" type="text" value="<?php echo esc_attr($instance['next_icon']); ?>">
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
                        <label for="<?php echo $this->get_field_id('container_margin'); ?>"><?php _e('Container Margin (px):', 'posts-grid-search'); ?></label>
                        <input class="widefat" id="<?php echo $this->get_field_id('container_margin'); ?>" name="<?php echo $this->get_field_name('container_margin'); ?>" type="number" value="<?php echo esc_attr($instance['container_margin']); ?>" min="0">
                    </p>
                    
                    <p>
                        <label for="<?php echo $this->get_field_id('container_border_radius'); ?>"><?php _e('Container Border Radius (px):', 'posts-grid-search'); ?></label>
                        <input class="widefat" id="<?php echo $this->get_field_id('container_border_radius'); ?>" name="<?php echo $this->get_field_name('container_border_radius'); ?>" type="number" value="<?php echo esc_attr($instance['container_border_radius']); ?>" min="0">
                    </p>
                    
                    <h4><?php _e('Card Styling', 'posts-grid-search'); ?></h4>
                    
                    <p>
                        <label for="<?php echo $this->get_field_id('card_bg'); ?>"><?php _e('Card Background:', 'posts-grid-search'); ?></label>
                        <input class="widefat" id="<?php echo $this->get_field_id('card_bg'); ?>" name="<?php echo $this->get_field_name('card_bg'); ?>" type="color" value="<?php echo esc_attr($instance['card_bg']); ?>">
                    </p>
                    
                    <p>
                        <label for="<?php echo $this->get_field_id('card_padding'); ?>"><?php _e('Card Padding (px):', 'posts-grid-search'); ?></label>
                        <input class="widefat" id="<?php echo $this->get_field_id('card_padding'); ?>" name="<?php echo $this->get_field_name('card_padding'); ?>" type="number" value="<?php echo esc_attr($instance['card_padding']); ?>" min="0">
                    </p>
                    
                    <p>
                        <label for="<?php echo $this->get_field_id('card_border_radius'); ?>"><?php _e('Card Border Radius (px):', 'posts-grid-search'); ?></label>
                        <input class="widefat" id="<?php echo $this->get_field_id('card_border_radius'); ?>" name="<?php echo $this->get_field_name('card_border_radius'); ?>" type="number" value="<?php echo esc_attr($instance['card_border_radius']); ?>" min="0">
                    </p>
                    
                    <p>
                        <label for="<?php echo $this->get_field_id('card_shadow'); ?>"><?php _e('Card Shadow:', 'posts-grid-search'); ?></label>
                        <input class="widefat" id="<?php echo $this->get_field_id('card_shadow'); ?>" name="<?php echo $this->get_field_name('card_shadow'); ?>" type="text" value="<?php echo esc_attr($instance['card_shadow']); ?>">
                        <small><?php _e('CSS box-shadow value (e.g., 0 2px 10px rgba(0, 0, 0, 0.1))', 'posts-grid-search'); ?></small>
                    </p>
                    
                    <h4><?php _e('Typography', 'posts-grid-search'); ?></h4>
                    
                    <p>
                        <label for="<?php echo $this->get_field_id('title_color'); ?>"><?php _e('Title Color:', 'posts-grid-search'); ?></label>
                        <input class="widefat" id="<?php echo $this->get_field_id('title_color'); ?>" name="<?php echo $this->get_field_name('title_color'); ?>" type="color" value="<?php echo esc_attr($instance['title_color']); ?>">
                    </p>
                    
                    <p>
                        <label for="<?php echo $this->get_field_id('title_font_size'); ?>"><?php _e('Title Font Size (px):', 'posts-grid-search'); ?></label>
                        <input class="widefat" id="<?php echo $this->get_field_id('title_font_size'); ?>" name="<?php echo $this->get_field_name('title_font_size'); ?>" type="number" value="<?php echo esc_attr($instance['title_font_size']); ?>" min="10">
                    </p>
                    
                    <p>
                        <label for="<?php echo $this->get_field_id('excerpt_color'); ?>"><?php _e('Excerpt Color:', 'posts-grid-search'); ?></label>
                        <input class="widefat" id="<?php echo $this->get_field_id('excerpt_color'); ?>" name="<?php echo $this->get_field_name('excerpt_color'); ?>" type="color" value="<?php echo esc_attr($instance['excerpt_color']); ?>">
                    </p>
                    
                    <p>
                        <label for="<?php echo $this->get_field_id('excerpt_font_size'); ?>"><?php _e('Excerpt Font Size (px):', 'posts-grid-search'); ?></label>
                        <input class="widefat" id="<?php echo $this->get_field_id('excerpt_font_size'); ?>" name="<?php echo $this->get_field_name('excerpt_font_size'); ?>" type="number" value="<?php echo esc_attr($instance['excerpt_font_size']); ?>" min="10">
                    </p>
                    
                    <p>
                        <label for="<?php echo $this->get_field_id('meta_color'); ?>"><?php _e('Meta Color:', 'posts-grid-search'); ?></label>
                        <input class="widefat" id="<?php echo $this->get_field_id('meta_color'); ?>" name="<?php echo $this->get_field_name('meta_color'); ?>" type="color" value="<?php echo esc_attr($instance['meta_color']); ?>">
                    </p>
                    
                    <h4><?php _e('Hover Effects', 'posts-grid-search'); ?></h4>
                    
                    <p>
                        <label for="<?php echo $this->get_field_id('hover_transform'); ?>"><?php _e('Hover Transform:', 'posts-grid-search'); ?></label>
                        <select class="widefat" id="<?php echo $this->get_field_id('hover_transform'); ?>" name="<?php echo $this->get_field_name('hover_transform'); ?>">
                            <option value="none" <?php selected($instance['hover_transform'], 'none'); ?>><?php _e('None', 'posts-grid-search'); ?></option>
                            <option value="translateY(-5px)" <?php selected($instance['hover_transform'], 'translateY(-5px)'); ?>><?php _e('Lift Up', 'posts-grid-search'); ?></option>
                            <option value="scale(1.05)" <?php selected($instance['hover_transform'], 'scale(1.05)'); ?>><?php _e('Scale Up', 'posts-grid-search'); ?></option>
                            <option value="rotate(2deg)" <?php selected($instance['hover_transform'], 'rotate(2deg)'); ?>><?php _e('Rotate', 'posts-grid-search'); ?></option>
                        </select>
                    </p>
                </div>
                
                <!-- Style Tab -->
                <div class="pgs-tab-content" data-tab="style">
                    <h4><?php _e('Pagination Colors', 'posts-grid-search'); ?></h4>
                    
                    <p>
                        <label for="<?php echo $this->get_field_id('pagination_bg'); ?>"><?php _e('Background Color:', 'posts-grid-search'); ?></label>
                        <input class="widefat" id="<?php echo $this->get_field_id('pagination_bg'); ?>" name="<?php echo $this->get_field_name('pagination_bg'); ?>" type="color" value="<?php echo esc_attr($instance['pagination_bg']); ?>">
                    </p>
                    
                    <p>
                        <label for="<?php echo $this->get_field_id('pagination_active_color'); ?>"><?php _e('Active Color:', 'posts-grid-search'); ?></label>
                        <input class="widefat" id="<?php echo $this->get_field_id('pagination_active_color'); ?>" name="<?php echo $this->get_field_name('pagination_active_color'); ?>" type="color" value="<?php echo esc_attr($instance['pagination_active_color']); ?>">
                    </p>
                    
                    <p>
                        <label for="<?php echo $this->get_field_id('pagination_text_color'); ?>"><?php _e('Text Color:', 'posts-grid-search'); ?></label>
                        <input class="widefat" id="<?php echo $this->get_field_id('pagination_text_color'); ?>" name="<?php echo $this->get_field_name('pagination_text_color'); ?>" type="color" value="<?php echo esc_attr($instance['pagination_text_color']); ?>">
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
        $instance['template'] = (!empty($new_instance['template'])) ? sanitize_text_field($new_instance['template']) : 'card';
        $instance['columns'] = (!empty($new_instance['columns'])) ? intval($new_instance['columns']) : 3;
        $instance['posts_per_page'] = (!empty($new_instance['posts_per_page'])) ? intval($new_instance['posts_per_page']) : 6;
        $instance['show_pagination'] = !empty($new_instance['show_pagination']) ? 1 : 0;
        $instance['pagination_style'] = (!empty($new_instance['pagination_style'])) ? sanitize_text_field($new_instance['pagination_style']) : 'numbers';
        $instance['prev_icon'] = (!empty($new_instance['prev_icon'])) ? sanitize_text_field($new_instance['prev_icon']) : '←';
        $instance['next_icon'] = (!empty($new_instance['next_icon'])) ? sanitize_text_field($new_instance['next_icon']) : '→';
        $instance['show_excerpt'] = !empty($new_instance['show_excerpt']) ? 1 : 0;
        $instance['show_author'] = !empty($new_instance['show_author']) ? 1 : 0;
        $instance['show_date'] = !empty($new_instance['show_date']) ? 1 : 0;
        
        // Style settings
        $instance['container_bg'] = (!empty($new_instance['container_bg'])) ? sanitize_hex_color($new_instance['container_bg']) : '';
        $instance['container_padding'] = (!empty($new_instance['container_padding'])) ? intval($new_instance['container_padding']) : 20;
        $instance['container_margin'] = (!empty($new_instance['container_margin'])) ? intval($new_instance['container_margin']) : 0;
        $instance['container_border_radius'] = (!empty($new_instance['container_border_radius'])) ? intval($new_instance['container_border_radius']) : 8;
        $instance['card_bg'] = (!empty($new_instance['card_bg'])) ? sanitize_hex_color($new_instance['card_bg']) : '#ffffff';
        $instance['card_padding'] = (!empty($new_instance['card_padding'])) ? intval($new_instance['card_padding']) : 20;
        $instance['card_border_radius'] = (!empty($new_instance['card_border_radius'])) ? intval($new_instance['card_border_radius']) : 8;
        $instance['card_shadow'] = (!empty($new_instance['card_shadow'])) ? sanitize_text_field($new_instance['card_shadow']) : '0 2px 10px rgba(0, 0, 0, 0.1)';
        $instance['title_color'] = (!empty($new_instance['title_color'])) ? sanitize_hex_color($new_instance['title_color']) : '#1a202c';
        $instance['title_font_size'] = (!empty($new_instance['title_font_size'])) ? intval($new_instance['title_font_size']) : 18;
        $instance['excerpt_color'] = (!empty($new_instance['excerpt_color'])) ? sanitize_hex_color($new_instance['excerpt_color']) : '#4a5568';
        $instance['excerpt_font_size'] = (!empty($new_instance['excerpt_font_size'])) ? intval($new_instance['excerpt_font_size']) : 14;
        $instance['meta_color'] = (!empty($new_instance['meta_color'])) ? sanitize_hex_color($new_instance['meta_color']) : '#718096';
        $instance['hover_transform'] = (!empty($new_instance['hover_transform'])) ? sanitize_text_field($new_instance['hover_transform']) : 'translateY(-5px)';
        $instance['pagination_bg'] = (!empty($new_instance['pagination_bg'])) ? sanitize_hex_color($new_instance['pagination_bg']) : '#1a202c';
        $instance['pagination_active_color'] = (!empty($new_instance['pagination_active_color'])) ? sanitize_hex_color($new_instance['pagination_active_color']) : '#14b8a6';
        $instance['pagination_text_color'] = (!empty($new_instance['pagination_text_color'])) ? sanitize_hex_color($new_instance['pagination_text_color']) : '#ffffff';

        return $instance;
    }
}