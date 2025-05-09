<?php
/**
 * Plugin Name: Custom REST API Helper- Remove HTML from Posts
 * Description: Extends the WP REST API to fetch all posts with clean content (no HTML) prepared for our AI Bot.
 * Version: 1.13
 * Author: Anton T.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Register Custom REST API Route
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/posts/', [
        'methods'  => 'GET',
        'callback' => 'custom_get_clean_posts',
        'permission_callback' => '__return_true', // Publicly accessible
    ]);
});

/**
 * Fetch all posts and remove HTML from content
 */
function custom_get_clean_posts($request) {
    $excluded_category = array(143); // Category to exclude

    $args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => -1, // Get all posts
        'category__not_in' => $excluded_category,
    );
    
    $query = new WP_Query($args);
    $posts = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $posts[] = [
                'title'   => get_the_title(),
                'content' => preg_replace('/\s+/', ' ', wp_strip_all_tags(get_the_content())), // Remove HTML
                // 'link'    => get_permalink(), For the moment we won't need links as the AI does not return them properly
            ];
        }
        wp_reset_postdata();
    }

    return rest_ensure_response($posts);
}