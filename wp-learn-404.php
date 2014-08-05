<?php
/*
Plugin Name: Learn 404
Plugin URI:
Description: Learn why 404 pages are being triggered on your site.
Version: 0.1
Author: Timothy Wood (@codearachnid)
Author URI: http://codearachnid.com
Text Domain: learn-404
*/

define( 'WPLEARN404', 'learn-404' );

function learn_404_init(){
  $labels = array(
		'name'               => _x( 'Learn 404', 'post type general name', 'learn-404' ),
		'singular_name'      => _x( 'Learn 404', 'post type singular name', 'learn-404' ),
		'menu_name'          => _x( 'Learn 404', 'admin menu', 'learn-404' ),
		'name_admin_bar'     => _x( 'Learn 404', 'add new on admin bar', 'learn-404' ),
		'edit_item'          => __( 'Edit 404', 'learn-404' ),
		'view_item'          => __( 'View 404', 'learn-404' ),
		'all_items'          => __( 'All 404', 'learn-404' ),
		'search_items'       => __( 'Search 404', 'learn-404' )
	);
	
	$args = array(
		'labels'             => $labels,
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array()
	);

	register_post_type( WPLEARN404', $args );
}

function learn_404_checker() {
    global $wp_query, $wpdb;
    if ( $$wp_query->is_404() ) {
      $post_404_args = array(
        'post_title' => '',
        'post_content' => json_encode( $wp_query ),
        'post_type' => WPLEARN404,
        'post_status' => 'pending',
        'ping_status' => 'closed',
        'comment_status' => 'closed'
      );
      $post_404_id = wp_insert_post( $post_404_args );
      if( !is_wp_error( $post_404_id ) ){
        add_post_meta($post_404_id, 'HTTP_REFERER', $_SERVER['HTTP_REFERER'], true ); // where they came from 
        add_post_meta($post_404_id, 'REQUEST_URI', $_SERVER['REQUEST_URI'], true ); // where they wanted to go
        add_post_meta($post_404_id, 'QUERY_STRING', $_SERVER['QUERY_STRING'], true );
        add_post_meta($post_404_id, 'HTTP_USER_AGENT', $_SERVER['HTTP_USER_AGENT'], true );
      }
    }
}


add_action( 'init', 'learn_404_init' );
add_filter( 'template_redirect', 'learn_404_checker', 1000 );
