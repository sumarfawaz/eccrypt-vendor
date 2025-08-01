<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// ========== POST DUPLICATOR ==========

function pd_duplicate_post_as_draft(){
    global $wpdb;

    if (
        ! ( isset($_GET['post']) || isset($_POST['post']) || ( isset($_REQUEST['action']) && 'pd_duplicate_post_as_draft' == $_REQUEST['action'] ) )
    ) {
        wp_die('No post to duplicate has been supplied!');
    }

    $post_id = (isset($_GET['post']) ? absint($_GET['post']) : absint($_POST['post']));
    $post = get_post($post_id);

    $current_user = wp_get_current_user();
    $new_post_author = $current_user->ID;

    if ($post) {
        $args = array(
            'comment_status' => $post->comment_status,
            'ping_status'    => $post->ping_status,
            'post_author'    => $new_post_author,
            'post_content'   => $post->post_content,
            'post_excerpt'   => $post->post_excerpt,
            'post_name'      => $post->post_name . '-copy',
            'post_parent'    => $post->post_parent,
            'post_password'  => $post->post_password,
            'post_status'    => 'draft',
            'post_title'     => $post->post_title . ' (Copy)',
            'post_type'      => $post->post_type,
            'to_ping'        => $post->to_ping,
            'menu_order'     => $post->menu_order
        );

        $new_post_id = wp_insert_post($args);

        // Copy taxonomies
        $taxonomies = get_object_taxonomies($post->post_type);
        foreach ($taxonomies as $taxonomy) {
            $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
            wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
        }

        // Copy post meta
        $post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
        if ($post_meta_infos) {
            foreach ($post_meta_infos as $meta_info) {
                add_post_meta($new_post_id, $meta_info->meta_key, maybe_unserialize($meta_info->meta_value));
            }
        }

        wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id));
        exit;
    } else {
        wp_die('Post creation failed, original post not found.');
    }
}
add_action('admin_action_pd_duplicate_post_as_draft', 'pd_duplicate_post_as_draft');

function pd_duplicate_post_link( $actions, $post ) {
    if (current_user_can('edit_posts')) {
        $actions['duplicate'] = '<a href="' . wp_nonce_url('admin.php?action=pd_duplicate_post_as_draft&post=' . $post->ID, basename(__FILE__), 'duplicate_nonce' ) . '" title="Duplicate this item" rel="permalink">Duplicate</a>';
    }
    return $actions;
}
add_filter('post_row_actions', 'pd_duplicate_post_link', 10, 2);
add_filter('page_row_actions', 'pd_duplicate_post_link', 10, 2);

// ========== TAXONOMY TERM DUPLICATOR ==========

function pd_duplicate_taxonomy_term_link( $actions, $term ) {
    if ( current_user_can( 'manage_categories' ) ) {
        $url = wp_nonce_url(
            admin_url( 'edit-tags.php?action=pd_duplicate_taxonomy_term&taxonomy=' . $term->taxonomy . '&tag_ID=' . $term->term_id ),
            'pd_duplicate_taxonomy_term_' . $term->term_id
        );
        $actions['duplicate'] = '<a href="' . $url . '" title="Duplicate this term">Duplicate</a>';
    }
    return $actions;
}
add_filter( 'tag_row_actions', 'pd_duplicate_taxonomy_term_link', 10, 2 );

function pd_duplicate_taxonomy_term() {
    if (
        ! isset($_GET['tag_ID'], $_GET['taxonomy']) ||
        ! current_user_can('manage_categories') ||
        ! wp_verify_nonce( $_GET['_wpnonce'], 'pd_duplicate_taxonomy_term_' . $_GET['tag_ID'] )
    ) {
        wp_die('Unauthorized or missing parameters.');
    }

    $term_id = absint($_GET['tag_ID']);
    $taxonomy = sanitize_text_field($_GET['taxonomy']);

    $term = get_term($term_id, $taxonomy);

    if ( is_wp_error($term) || !$term ) {
        wp_die('Original term not found.');
    }

    // Create new term
    $new_term = wp_insert_term($term->name . ' (Copy)', $taxonomy, [
        'description' => $term->description,
        'parent' => $term->parent,
        'slug' => sanitize_title($term->slug . '-copy'),
    ]);

    if ( is_wp_error($new_term) ) {
        wp_die('Term duplication failed: ' . $new_term->get_error_message());
    }

    $new_term_id = $new_term['term_id'];

    // Copy term meta if exists
    $term_meta = get_term_meta($term_id);
    foreach ($term_meta as $meta_key => $meta_values) {
        foreach ($meta_values as $meta_value) {
            add_term_meta($new_term_id, $meta_key, maybe_unserialize($meta_value));
        }
    }

    wp_redirect( admin_url( 'edit-tags.php?taxonomy=' . $taxonomy ) );
    exit;
}
add_action('admin_action_pd_duplicate_taxonomy_term', 'pd_duplicate_taxonomy_term');