<?php
/*
Plugin Name: Infinite Masonry Post Grid With Loading effect
Description: Creates an infinite scrollable post grid. [post_grid] [post_grid order="DESC" exclude_category_slug="category-slug"]

Version: 1.0
Author: Hassan Naqvi
*/

// Enqueue scripts and styles
function ipg_enqueue_scripts() {
    // Enqueue JavaScript files
    wp_enqueue_script('masonry', plugin_dir_url(__FILE__) . 'js/masonry.pkgd.min.js', array('jquery'), '1.0', true);
    wp_enqueue_script('imagesloaded', plugin_dir_url(__FILE__) . 'js/imagesloaded.pkgd.min.js', array('jquery'), '1.0', true);
    wp_enqueue_script('classie', plugin_dir_url(__FILE__) . 'js/classie.js', array(), '1.0', true);
    wp_enqueue_script('colorfinder', plugin_dir_url(__FILE__) . 'js/colorfinder-1.1.js', array(), '1.0', true);
    wp_enqueue_script('gridScrollFx', plugin_dir_url(__FILE__) . 'js/gridScrollFx.js', array('jquery'), '1.0', true);

    // Enqueue CSS files
    wp_enqueue_style('normalize', plugin_dir_url(__FILE__) . 'css/normalize.css', array(), '1.0');
    wp_enqueue_style('demo', plugin_dir_url(__FILE__) . 'css/demo.css', array(), '1.0');
    wp_enqueue_style('component', plugin_dir_url(__FILE__) . 'css/component.css', array(), '1.0');

    // Enqueue Modernizr script
    wp_enqueue_script('modernizr', plugin_dir_url(__FILE__) . 'js/modernizr.custom.js', array(), '1.0', false);

    // Initialize GridScrollFx script
    wp_add_inline_script('gridScrollFx', 'new GridScrollFx( document.getElementById( "grid" ), { viewportFactor : 0.4 });', 'after');
}
add_action('wp_enqueue_scripts', 'ipg_enqueue_scripts');


// WordPress function to retrieve posts and generate HTML markup
function get_post_grid($order = 'DESC', $exclude_category_slug = '') {
    if (\Elementor\Plugin::instance()->editor->is_edit_mode()) {
        return ''; // Return an empty string to hide the shortcode in Elementor editor
    }
    
    $output = ''; // Initialize output variable

    // Get category ID by slug
    $exclude_category_id = '';
    if (!empty($exclude_category_slug)) {
        $exclude_category = get_category_by_slug($exclude_category_slug);
        if ($exclude_category) {
            $exclude_category_id = $exclude_category->term_id;
        }
    }

    // Query WordPress posts
    $args = array(
        'post_type' => 'post', // You can change post type here if needed
        'posts_per_page' => -1, // Retrieve all posts
        'post_status' => 'publish', // Retrieve only published posts
        'order' => $order, // Order of the posts
    );

    // Exclude specific category if provided
    if (!empty($exclude_category_id)) {
        $args['category__not_in'] = array($exclude_category_id);
    }

    $posts_query = new WP_Query($args);

    // Check if posts are found
    if ($posts_query->have_posts()) {
        $output .= '<div class="container-post">';
        $output .= '<section class="grid-wrap">';
        $output .= '<ul class="grid swipe-right" id="grid">';
        
        // Loop through each post
        while ($posts_query->have_posts()) {
            $posts_query->the_post();
            $output .= '<li>';
            $output .= '<a href="' . get_permalink() . '">';

            // Check if post has a featured image
            if (has_post_thumbnail()) {
                $output .= '<img src="' . get_the_post_thumbnail_url() . '" alt="' . get_the_title() . '">';
            } else {
                // Fallback image path
                $fallback_image = plugins_url('img/dummy.png', __FILE__);
                $output .= '<img src="' . $fallback_image . '" alt="Fallback Image">';
            }

            $output .= '<h3>' . get_the_title() . '</h3>';
            $output .= '</a>';
            $output .= '</li>';
        }
        
        $output .= '</ul>';
        $output .= '</section>';
        $output .= '</div>';
        
        // Reset post data
        wp_reset_postdata();
    } else {
        $output .= '<p>No posts found</p>';
    }

    return $output; // Return the generated HTML markup
}


// WordPress shortcode to display post grid
function post_grid_shortcode($atts) {
    // Extract shortcode attributes
    $atts = shortcode_atts(array(
        'order' => 'DESC',
        'exclude_category_slug' => '',
    ), $atts);

    // Call the function to retrieve post grid HTML markup with specified attributes
    $post_grid = get_post_grid($atts['order'], $atts['exclude_category_slug']);
    
    return $post_grid; // Return the HTML markup
}
add_shortcode('post_grid', 'post_grid_shortcode'); // Register shortcode




// Function to display the settings page content for Infinite Post Grid
function infinite_post_grid_settings_page() {
    ?>
    <div class="wrap">
<div class="info-box">
    <h2>Infinite Scrollable Post Grid</h2>
    <p>This plugin creates an infinite scrollable post grid.</p>
    <h3>Shortcode Usage</h3>
    <p>Use the following shortcode to display the post grid:</p>
    <pre>[post_grid]</pre>
    <p>To customize the display, you can use additional parameters:</p>
    <pre>[post_grid order="DESC" exclude_category_slug="category-slug"]</pre>
    <p>Adjust the parameters as needed. For instance, "order" specifies the order of posts, and "exclude_category_slug" excludes posts from a specific category identified by its slug.</p>
</div>

    </div>
    <?php
}

// Function to add the settings page to the admin menu
function infinite_post_grid_add_menu() {
    add_options_page('Infinite Post Grid Settings', 'Infinite Post Grid', 'manage_options', 'infinite-post-grid-settings', 'infinite_post_grid_settings_page');
}

// Hook to add the settings page
add_action('admin_menu', 'infinite_post_grid_add_menu');

