<?php
/**
 * Search Filter Widget Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class PGS_Search_Filter_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'pgs_search_filter',
            __('Posts Search Filter Pro', 'posts-grid-search'),
            array(
                'description' => __('Professional search filter for Posts Grid widget with advanced styling options.', 'posts-grid-search'),
                'classname' => 'pgs-search-filter-widget'
            )
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        $placeholder = !empty($instance['placeholder']) ? $instance['placeholder'] : __('Find by title or author...', 'posts-grid-search');
        
        // Generate custom styles
        $custom_styles = $this->generate_custom_styles($instance);
        if ($custom_styles) {
            echo '<style>' . $custom_styles . '</style>';
        }
        
        ?>
        <div class="pgs-search-filter">
            <div class="pgs-search-container">
                <div class="pgs-search-input-wrapper">
                    <svg class="pgs-search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    <input 
                        type="text" 
                        id="pgs-search-input" 
                        class="pgs-search-input" 
                        placeholder="<?php echo esc_attr($placeholder); ?>"
                        data-target-widget="<?php echo esc_attr($instance['target_widget']); ?>"
                    >
                    <button type="button" class="pgs-search-clear" id="pgs-search-clear" style="display: none;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="pgs-search-results-info" id="pgs-search-results-info" style="display: none;">
                <span id="pgs-results-count"></span>
                <button type="button" class="pgs-clear-search" id="pgs-clear-search">
                    <?php _e('Clear search', 'posts-grid-search'); ?>
                </button>
            </div>
        </div>
        <?php
        
        echo $args['after_widget'];
    }
    
    private function generate_custom_styles($instance) {
        $widget_id = $this->id;
        $styles = '';
        
        // Search input styles
        if (!empty($instance['search_bg'])) {
            $styles .= "#{$widget_id} .pgs-search-input { background-color: {$instance['search_bg']}; }";
        }
        if (!empty($instance['search_text_color'])) {
            $styles .= "#{$widget_id} .pgs-search-input, #{$widget_id} .pgs-search-icon, #{$widget_id} .pgs-search-clear { color: {$instance['search_text_color']}; }";
            $styles .= "#{$widget_id} .pgs-search-input::placeholder { color: {$instance['search_text_color']}; opacity: 0.7; }";
        }
        if (!empty($instance['search_border_color'])) {
            $styles .= "#{$widget_id} .pgs-search-input { border-color: {$instance['search_border_color']}; }";
            $styles .= "#{$widget_id} .pgs-search-input:focus { border-color: {$instance['search_border_color']}; box-shadow: 0 0 0 3px rgba(" . $this->hex_to_rgb($instance['search_border_color']) . ", 0.1); }";
        }
        if (!empty($instance['search_border_radius'])) {
            $styles .= "#{$widget_id} .pgs-search-input { border-radius: {$instance['search_border_radius']}px; }";
        }
        if (!empty($instance['search_padding'])) {
            $styles .= "#{$widget_id} .pgs-search-input { padding: {$instance['search_padding']}px 40px; }";
        }
        
        // Container styles
        if (!empty($instance['container_bg'])) {
            $styles .= "#{$widget_id} .pgs-search-filter { background-color: {$instance['container_bg']}; }";
        }
        if (!empty($instance['container_padding'])) {
            $styles .= "#{$widget_id} .pgs-search-filter { padding: {$instance['container_padding']}px; }";
        }
        if (!empty($instance['container_border_radius'])) {
            $styles .= "#{$widget_id} .pgs-search-filter { border-radius: {$instance['container_border_radius']}px; }";
        }
        
        return $styles;
    }
    
    private function hex_to_rgb($hex) {
        $hex = ltrim($hex, '#');
        return implode(', ', array_map('hexdec', str_split($hex, 2)));
    }
    
    public function form($instance) {
        // Default values
        $defaults = array(
            'title' => '',
            'placeholder' => __('Find by title or author...', 'posts-grid-search'),
            'target_widget' => '',
            // Style defaults
            'search_bg' => '#ffffff',
            'search_text_color' => '#1a202c',
            'search_border_color' => '#14b8a6',
            'search_border_radius' => '6',
            'search_padding' => '12',
            'container_bg' => '',
            'container_padding' => '0',
            'container_border_radius' => '0'
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
                        <label for="<?php echo $this->get_field_id('placeholder'); ?>"><?php _e('Placeholder text:', 'posts-grid-search'); ?></label>
                        <input class="widefat" id="<?php echo $this->get_field_id('placeholder'); ?>" name="<?php echo $this->get_field_name('placeholder'); ?>" type="text" value="<?php echo esc_attr($instance['placeholder']); ?>">
                    </p>
                    
                    <p>
                        <label for="<?php echo $this->get_field_id('target_widget'); ?>"><?php _e('Target Posts Grid Widget ID (optional):', 'posts-grid-search'); ?></label>
                        <input class="widefat" id="<?php echo $this->get_field_id('target_widget'); ?>" name="<?php echo $this->get_field_name('target_widget'); ?>" type="text" value="<?php echo esc_attr($instance['target_widget']); ?>">
                        <small><?php _e('Leave empty to target all Posts Grid widgets on the page.', 'posts-grid-search'); ?></small>
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
                    
                    <h4><?php _e('Search Input Styling', 'posts-grid-search'); ?></h4>
                    
                    <p>
                        <label for="<?php echo $this->get_field_id('search_bg'); ?>"><?php _e('Background Color:', 'posts-grid-search'); ?></label>
                        <input class="widefat" id="<?php echo $this->get_field_id('search_bg'); ?>" name="<?php echo $this->get_field_name('search_bg'); ?>" type="color" value="<?php echo esc_attr($instance['search_bg']); ?>">
                    </p>
                    
                    <p>
                        <label for="<?php echo $this->get_field_id('search_text_color'); ?>"><?php _e('Text Color:', 'posts-grid-search'); ?></label>
                        <input class="widefat" id="<?php echo $this->get_field_id('search_text_color'); ?>" name="<?php echo $this->get_field_name('search_text_color'); ?>" type="color" value="<?php echo esc_attr($instance['search_text_color']); ?>">
                    </p>
                    
                    <p>
                        <label for="<?php echo $this->get_field_id('search_border_color'); ?>"><?php _e('Border Color:', 'posts-grid-search'); ?></label>
                        <input class="widefat" id="<?php echo $this->get_field_id('search_border_color'); ?>" name="<?php echo $this->get_field_name('search_border_color'); ?>" type="color" value="<?php echo esc_attr($instance['search_border_color']); ?>">
                    </p>
                    
                    <p>
                        <label for="<?php echo $this->get_field_id('search_border_radius'); ?>"><?php _e('Border Radius (px):', 'posts-grid-search'); ?></label>
                        <input class="widefat" id="<?php echo $this->get_field_id('search_border_radius'); ?>" name="<?php echo $this->get_field_name('search_border_radius'); ?>" type="number" value="<?php echo esc_attr($instance['search_border_radius']); ?>" min="0">
                    </p>
                    
                    <p>
                        <label for="<?php echo $this->get_field_id('search_padding'); ?>"><?php _e('Input Padding (px):', 'posts-grid-search'); ?></label>
                        <input class="widefat" id="<?php echo $this->get_field_id('search_padding'); ?>" name="<?php echo $this->get_field_name('search_padding'); ?>" type="number" value="<?php echo esc_attr($instance['search_padding']); ?>" min="0">
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
        $instance['placeholder'] = (!empty($new_instance['placeholder'])) ? sanitize_text_field($new_instance['placeholder']) : '';
        $instance['target_widget'] = (!empty($new_instance['target_widget'])) ? sanitize_text_field($new_instance['target_widget']) : '';
        
        // Style settings
        $instance['search_bg'] = (!empty($new_instance['search_bg'])) ? sanitize_hex_color($new_instance['search_bg']) : '#ffffff';
        $instance['search_text_color'] = (!empty($new_instance['search_text_color'])) ? sanitize_hex_color($new_instance['search_text_color']) : '#1a202c';
        $instance['search_border_color'] = (!empty($new_instance['search_border_color'])) ? sanitize_hex_color($new_instance['search_border_color']) : '#14b8a6';
        $instance['search_border_radius'] = (!empty($new_instance['search_border_radius'])) ? intval($new_instance['search_border_radius']) : 6;
        $instance['search_padding'] = (!empty($new_instance['search_padding'])) ? intval($new_instance['search_padding']) : 12;
        $instance['container_bg'] = (!empty($new_instance['container_bg'])) ? sanitize_hex_color($new_instance['container_bg']) : '';
        $instance['container_padding'] = (!empty($new_instance['container_padding'])) ? intval($new_instance['container_padding']) : 0;
        $instance['container_border_radius'] = (!empty($new_instance['container_border_radius'])) ? intval($new_instance['container_border_radius']) : 0;
        
        return $instance;
    }
}