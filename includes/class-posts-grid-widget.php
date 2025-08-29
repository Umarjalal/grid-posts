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
            __('Posts Grid', 'posts-grid-search'),
            array(
                'description' => __('Display posts in customizable grid layout with pagination.', 'posts-grid-search'),
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
        $prev_icon = !empty($instance['prev_icon']) ? $instance['prev_icon'] : '←';
        $next_icon = !empty($instance['next_icon']) ? $instance['next_icon'] : '→';
        
        // Get current page
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        
        $query_args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $posts_per_page,
            'paged' => $paged
        );
        
        $posts_query = new WP_Query($query_args);
        
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
            $this->render_pagination($posts_query, $pagination_style, $prev_icon, $next_icon, $instance);
        }
        
        echo '</div>'; // .pgs-posts-grid
        
        wp_reset_postdata();
        echo $args['after_widget'];
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
        
        // Check if template is a saved template (numeric ID)
        if (is_numeric($template) && get_post_status($template) === 'publish') {
            $saved_template_content = get_post_field('post_content', $template);
            // Assuming saved template content is HTML/PHP markup
            echo '<article class="pgs-post-saved">';
            echo '<a href="' . esc_url($permalink) . '" class="pgs-post-link">';
            // Replace placeholders in saved template content
            $template_output = $saved_template_content;
            $template_output = str_replace('{{title}}', esc_html($title), $template_output);
            $template_output = str_replace('{{excerpt}}', $show_excerpt ? esc_html($excerpt) : '', $template_output);
            $template_output = str_replace('{{author}}', $show_author ? esc_html($author) : '', $template_output);
            $template_output = str_replace('{{date}}', $show_date ? esc_html($date) : '', $template_output);
            $template_output = str_replace('{{thumbnail}}', $thumbnail ? $thumbnail : '', $template_output);
            echo $template_output;
            echo '</a>';
            echo '</article>';
        } else {
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
    
    private function render_pagination($query, $style, $prev_icon, $next_icon, $instance) {
        $current_page = max(1, get_query_var('paged'));
        $total_pages = $query->max_num_pages;
        
        $pagination_bg = !empty($instance['pagination_bg']) ? $instance['pagination_bg'] : '#1a202c';
        $pagination_active_color = !empty($instance['pagination_active_color']) ? $instance['pagination_active_color'] : '#14b8a6';
        $pagination_text_color = !empty($instance['pagination_text_color']) ? $instance['pagination_text_color'] : '#ffffff';
        
        echo '<div class="pgs-pagination" style="--pagination-bg: ' . esc_attr($pagination_bg) . '; --pagination-active: ' . esc_attr($pagination_active_color) . '; --pagination-text: ' . esc_attr($pagination_text_color) . ';">';
        
        if ($style === 'numbers') {
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
            
        } elseif ($style === 'simple') {
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
            
        } elseif ($style === 'arrows') {
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
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $template = !empty($instance['template']) ? $instance['template'] : 'card';
        $columns = !empty($instance['columns']) ? $instance['columns'] : '3';
        $posts_per_page = !empty($instance['posts_per_page']) ? $instance['posts_per_page'] : '6';
        $show_pagination = !empty($instance['show_pagination']) ? true : false;
        $pagination_style = !empty($instance['pagination_style']) ? $instance['pagination_style'] : 'numbers';
        $prev_icon = !empty($instance['prev_icon']) ? $instance['prev_icon'] : '←';
        $next_icon = !empty($instance['next_icon']) ? $instance['next_icon'] : '→';
        $pagination_bg = !empty($instance['pagination_bg']) ? $instance['pagination_bg'] : '#1a202c';
        $pagination_active_color = !empty($instance['pagination_active_color']) ? $instance['pagination_active_color'] : '#14b8a6';
        $pagination_text_color = !empty($instance['pagination_text_color']) ? $instance['pagination_text_color'] : '#ffffff';
        $show_excerpt = !empty($instance['show_excerpt']) ? true : false;
        $show_author = !empty($instance['show_author']) ? true : false;
        $show_date = !empty($instance['show_date']) ? true : false;
        
        // Fetch saved templates (assuming stored as custom post type 'pgs_template')
        $saved_templates = get_posts(array(
            'post_type' => 'pgs_template',
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ));
        ?>
        <div class="pgs-widget-form">
            <p>
                <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'posts-grid-search'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
            </p>
            
            <h4><?php _e('Layout Settings', 'posts-grid-search'); ?></h4>
            
            <p>
                <label for="<?php echo $this->get_field_id('template'); ?>"><?php _e('Template:', 'posts-grid-search'); ?></label>
                <select class="widefat" id="<?php echo $this->get_field_id('template'); ?>" name="<?php echo $this->get_field_name('template'); ?>">
                    <optgroup label="<?php _e('Default Templates', 'posts-grid-search'); ?>">
                        <option value="card" <?php selected($template, 'card'); ?>><?php _e('Card', 'posts-grid-search'); ?></option>
                        <option value="list" <?php selected($template, 'list'); ?>><?php _e('List', 'posts-grid-search'); ?></option>
                        <option value="minimal" <?php selected($template, 'minimal'); ?>><?php _e('Minimal', 'posts-grid-search'); ?></option>
                    </optgroup>
                    <?php if (!empty($saved_templates)) : ?>
                        <optgroup label="<?php _e('Saved Templates', 'posts-grid-search'); ?>">
                            <?php foreach ($saved_templates as $saved_template) : ?>
                                <option value="<?php echo esc_attr($saved_template->ID); ?>" <?php selected($template, $saved_template->ID); ?>>
                                    <?php echo esc_html($saved_template->post_title); ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                </select>
            </p>
            
            <p>
                <label for="<?php echo $this->get_field_id('columns'); ?>"><?php _e('Columns:', 'posts-grid-search'); ?></label>
                <select class="widefat" id="<?php echo $this->get_field_id('columns'); ?>" name="<?php echo $this->get_field_name('columns'); ?>">
                    <option value="1" <?php selected($columns, '1'); ?>>1</option>
                    <option value="2" <?php selected($columns, '2'); ?>>2</option>
                    <option value="3" <?php selected($columns, '3'); ?>>3</option>
                    <option value="4" <?php selected($columns, '4'); ?>>4</option>
                </select>
            </p>
            
            <p>
                <label for="<?php echo $this->get_field_id('posts_per_page'); ?>"><?php _e('Posts per page:', 'posts-grid-search'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('posts_per_page'); ?>" name="<?php echo $this->get_field_name('posts_per_page'); ?>" type="number" value="<?php echo esc_attr($posts_per_page); ?>" min="1">
            </p>
            
            <h4><?php _e('Content Settings', 'posts-grid-search'); ?></h4>
            
            <p>
                <input class="checkbox" type="checkbox" <?php checked($show_excerpt, true); ?> id="<?php echo $this->get_field_id('show_excerpt'); ?>" name="<?php echo $this->get_field_name('show_excerpt'); ?>">
                <label for="<?php echo $this->get_field_id('show_excerpt'); ?>"><?php _e('Show excerpt', 'posts-grid-search'); ?></label>
            </p>
            
            <p>
                <input class="checkbox" type="checkbox" <?php checked($show_author, true); ?> id="<?php echo $this->get_field_id('show_author'); ?>" name="<?php echo $this->get_field_name('show_author'); ?>">
                <label for="<?php echo $this->get_field_id('show_author'); ?>"><?php _e('Show author', 'posts-grid-search'); ?></label>
            </p>
            
            <p>
                <input class="checkbox" type="checkbox" <?php checked($show_date, true); ?> id="<?php echo $this->get_field_id('show_date'); ?>" name="<?php echo $this->get_field_name('show_date'); ?>">
                <label for="<?php echo $this->get_field_id('show_date'); ?>"><?php _e('Show date', 'posts-grid-search'); ?></label>
            </p>
            
            <h4><?php _e('Pagination Settings', 'posts-grid-search'); ?></h4>
            
            <p>
                <input class="checkbox" type="checkbox" <?php checked($show_pagination, true); ?> id="<?php echo $this->get_field_id('show_pagination'); ?>" name="<?php echo $this->get_field_name('show_pagination'); ?>">
                <label for="<?php echo $this->get_field_id('show_pagination'); ?>"><?php _e('Show pagination', 'posts-grid-search'); ?></label>
            </p>
            
            <p>
                <label for="<?php echo $this->get_field_id('pagination_style'); ?>"><?php _e('Pagination style:', 'posts-grid-search'); ?></label>
                <select class="widefat" id="<?php echo $this->get_field_id('pagination_style'); ?>" name="<?php echo $this->get_field_name('pagination_style'); ?>">
                    <option value="numbers" <?php selected($pagination_style, 'numbers'); ?>><?php _e('Numbers', 'posts-grid-search'); ?></option>
                    <option value="simple" <?php selected($pagination_style, 'simple'); ?>><?php _e('Simple', 'posts-grid-search'); ?></option>
                    <option value="arrows" <?php selected($pagination_style, 'arrows'); ?>><?php _e('Arrows only', 'posts-grid-search'); ?></option>
                </select>
            </p>
            
            <p>
                <label for="<?php echo $this->get_field_id('prev_icon'); ?>"><?php _e('Previous icon:', 'posts-grid-search'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('prev_icon'); ?>" name="<?php echo $this->get_field_name('prev_icon'); ?>" type="text" value="<?php echo esc_attr($prev_icon); ?>">
            </p>
            
            <p>
                <label for="<?php echo $this->get_field_id('next_icon'); ?>"><?php _e('Next icon:', 'posts-grid-search'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('next_icon'); ?>" name="<?php echo $this->get_field_name('next_icon'); ?>" type="text" value="<?php echo esc_attr($next_icon); ?>">
            </p>
            
            <h4><?php _e('Pagination Colors', 'posts-grid-search'); ?></h4>
            
            <p>
                <label for="<?php echo $this->get_field_id('pagination_bg'); ?>"><?php _e('Background color:', 'posts-grid-search'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('pagination_bg'); ?>" name="<?php echo $this->get_field_name('pagination_bg'); ?>" type="color" value="<?php echo esc_attr($pagination_bg); ?>">
            </p>
            
            <p>
                <label for="<?php echo $this->get_field_id('pagination_active_color'); ?>"><?php _e('Active color:', 'posts-grid-search'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('pagination_active_color'); ?>" name="<?php echo $this->get_field_name('pagination_active_color'); ?>" type="color" value="<?php echo esc_attr($pagination_active_color); ?>">
            </p>
            
            <p>
                <label for="<?php echo $this->get_field_id('pagination_text_color'); ?>"><?php _e('Text color:', 'posts-grid-search'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('pagination_text_color'); ?>" name="<?php echo $this->get_field_name('pagination_text_color'); ?>" type="color" value="<?php echo esc_attr($pagination_text_color); ?>">
            </p>
        </div>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['template'] = (!empty($new_instance['template'])) ? sanitize_text_field($new_instance['template']) : 'card';
        $instance['columns'] = (!empty($new_instance['columns'])) ? intval($new_instance['columns']) : 3;
        $instance['posts_per_page'] = (!empty($new_instance['posts_per_page'])) ? intval($new_instance['posts_per_page']) : 6;
        $instance['show_pagination'] = !empty($new_instance['show_pagination']) ? 1 : 0;
        $instance['pagination_style'] = (!empty($new_instance['pagination_style'])) ? sanitize_text_field($new_instance['pagination_style']) : 'numbers';
        $instance['prev_icon'] = (!empty($new_instance['prev_icon'])) ? sanitize_text_field($new_instance['prev_icon']) : '←';
        $instance['next_icon'] = (!empty($new_instance['next_icon'])) ? sanitize_text_field($new_instance['next_icon']) : '→';
        $instance['pagination_bg'] = (!empty($new_instance['pagination_bg'])) ? sanitize_hex_color($new_instance['pagination_bg']) : '#1a202c';
        $instance['pagination_active_color'] = (!empty($new_instance['pagination_active_color'])) ? sanitize_hex_color($new_instance['pagination_active_color']) : '#14b8a6';
        $instance['pagination_text_color'] = (!empty($new_instance['pagination_text_color'])) ? sanitize_hex_color($new_instance['pagination_text_color']) : '#ffffff';
        $instance['show_excerpt'] = !empty($new_instance['show_excerpt']) ? 1 : 0;
        $instance['show_author'] = !empty($new_instance['show_author']) ? 1 : 0;
        $instance['show_date'] = !empty($new_instance['show_date']) ? 1 : 0;

        return $instance;
    }

}