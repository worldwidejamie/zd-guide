<?php
/**
 * File for registering custom post types and taxonomies.
 *
 * @package Wwj_Zdguide
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Register the custom post type for articles.
 */
function wwj_zdguide_register_post_type() {
    $labels = array(
        'name'                  => _x( 'Articles', 'Post type general name', 'wwj-zdguide' ),
        'singular_name'         => _x( 'Article', 'Post type singular name', 'wwj-zdguide' ),
        'menu_name'             => _x( 'Zendesk Guide', 'Admin Menu text', 'wwj-zdguide' ),
        'name_admin_bar'        => _x( 'Article', 'Add New on Toolbar', 'wwj-zdguide' ),
        'add_new'               => __( 'Add New', 'wwj-zdguide' ),
        'add_new_item'          => __( 'Add New Article', 'wwj-zdguide' ),
        'new_item'              => __( 'New Article', 'wwj-zdguide' ),
        'edit_item'             => __( 'Edit Article', 'wwj-zdguide' ),
        'view_item'             => __( 'View Article', 'wwj-zdguide' ),
        'all_items'             => __( 'All Articles', 'wwj-zdguide' ),
        'search_items'          => __( 'Search Articles', 'wwj-zdguide' ),
        'parent_item_colon'     => __( 'Parent Articles:', 'wwj-zdguide' ),
        'not_found'             => __( 'No articles found.', 'wwj-zdguide' ),
        'not_found_in_trash'    => __( 'No articles found in Trash.', 'wwj-zdguide' ),
        'featured_image'        => _x( 'Article Cover Image', 'Overrides the \'Featured Image\' phrase for this post type. Added in 4.3', 'wwj-zdguide' ),
        'set_featured_image'    => _x( 'Set cover image', 'Overrides the \'Set featured image\' phrase for this post type. Added in 4.3', 'wwj-zdguide' ),
        'remove_featured_image' => _x( 'Remove cover image', 'Overrides the \'Remove featured image\' phrase for this post type. Added in 4.3', 'wwj-zdguide' ),
        'use_featured_image'    => _x( 'Use as cover image', 'Overrides the \'Use as featured image\' phrase for this post type. Added in 4.3', 'wwj-zdguide' ),
        'archives'              => _x( 'Article archives', 'The post type archive label used in nav menus. Default \'Post Archives\'. Added in 4.4', 'wwj-zdguide' ),
        'insert_into_item'      => _x( 'Insert into article', 'Overrides the \'Insert into post\'/\'Insert into page\' phrase (used when inserting media into a post). Added in 4.4', 'wwj-zdguide' ),
        'uploaded_to_this_item' => _x( 'Uploaded to this article', 'Overrides the \'Uploaded to this post\'/\'Uploaded to this page\' phrase (used when viewing media attached to a post). Added in 4.4', 'wwj-zdguide' ),
        'filter_items_list'     => _x( 'Filter articles list', 'Screen reader text for the filter links heading on the post type listing screen. Default \'Filter posts list\'/\'Filter pages list\'. Added in 4.4', 'wwj-zdguide' ),
        'items_list_navigation' => _x( 'Articles list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default \'Posts list navigation\'/\'Pages list navigation\'. Added in 4.4', 'wwj-zdguide' ),
        'items_list'            => _x( 'Articles list', 'Screen reader text for the items list heading on the post type listing screen. Default \'Posts list\'/\'Pages list\'. Added in 4.4', 'wwj-zdguide' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'help-center' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 20,
        'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'revisions' ),
        'show_in_rest'       => true,
        'menu_icon'          => 'dashicons-book-alt',
    );

    register_post_type( 'zd_article', $args );
}
add_action( 'init', 'wwj_zdguide_register_post_type' );

/**
 * Register the custom taxonomies for categories and sections.
 */
function wwj_zdguide_register_taxonomies() {
    // Category taxonomy
    $category_labels = array(
        'name'              => _x( 'Categories', 'taxonomy general name', 'wwj-zdguide' ),
        'singular_name'     => _x( 'Category', 'taxonomy singular name', 'wwj-zdguide' ),
        'search_items'      => __( 'Search Categories', 'wwj-zdguide' ),
        'all_items'         => __( 'All Categories', 'wwj-zdguide' ),
        'parent_item'       => __( 'Parent Category', 'wwj-zdguide' ),
        'parent_item_colon' => __( 'Parent Category:', 'wwj-zdguide' ),
        'edit_item'         => __( 'Edit Category', 'wwj-zdguide' ),
        'update_item'       => __( 'Update Category', 'wwj-zdguide' ),
        'add_new_item'      => __( 'Add New Category', 'wwj-zdguide' ),
        'new_item_name'     => __( 'New Category Name', 'wwj-zdguide' ),
        'menu_name'         => __( 'Categories', 'wwj-zdguide' ),
    );

    $category_args = array(
        'hierarchical'      => true,
        'labels'            => $category_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'help-center/categories' ),
        'show_in_rest'      => true,
    );

    register_taxonomy( 'zd_category', array( 'zd_article' ), $category_args );

    // Section taxonomy
    $section_labels = array(
        'name'              => _x( 'Sections', 'taxonomy general name', 'wwj-zdguide' ),
        'singular_name'     => _x( 'Section', 'taxonomy singular name', 'wwj-zdguide' ),
        'search_items'      => __( 'Search Sections', 'wwj-zdguide' ),
        'all_items'         => __( 'All Sections', 'wwj-zdguide' ),
        'parent_item'       => __( 'Parent Section', 'wwj-zdguide' ),
        'parent_item_colon' => __( 'Parent Section:', 'wwj-zdguide' ),
        'edit_item'         => __( 'Edit Section', 'wwj-zdguide' ),
        'update_item'       => __( 'Update Section', 'wwj-zdguide' ),
        'add_new_item'      => __( 'Add New Section', 'wwj-zdguide' ),
        'new_item_name'     => __( 'New Section Name', 'wwj-zdguide' ),
        'menu_name'         => __( 'Sections', 'wwj-zdguide' ),
    );

    $section_args = array(
        'hierarchical'      => true,
        'labels'            => $section_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'help-center/sections' ),
        'show_in_rest'      => true,
    );

    register_taxonomy( 'zd_section', array( 'zd_article' ), $section_args );
}
add_action( 'init', 'wwj_zdguide_register_taxonomies' );
