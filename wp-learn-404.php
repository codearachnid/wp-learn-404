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

function learn_404_init() {
  
	$labels = array(
		'name'           => _x( 'Caught 404', 'post type general name', 'learn-404' ),
		'singular_name'  => _x( 'Learn 404', 'post type singular name', 'learn-404' ),
		'menu_name'      => _x( 'Learn 404', 'admin menu', 'learn-404' ),
		'name_admin_bar' => _x( 'Learn 404', 'add new on admin bar', 'learn-404' ),
		'edit_item'      => __( 'Edit 404', 'learn-404' ),
		'view_item'      => __( 'View 404', 'learn-404' ),
		'all_items'      => __( 'Caught 404', 'learn-404' ),
		'search_items'   => __( 'Search 404', 'learn-404')
		);

	$args = array(
		'labels'             => $labels, 
		'public'             => false,
		'publicly_queryable' => false, 
		'show_ui'            => true,
		'show_in_menu'       => 'tools.php', 
		'query_var'          => true,
		'capability_type'    => 'post', 
		'has_archive'        => true,
		'hierarchical'       => false, 
		'menu_position'      => null, 
		'supports'           => array( '' )
		);

	register_post_type( WPLEARN404, $args );
}

function learn_404_add_meta_box(){
	remove_meta_box( 'submitdiv', WPLEARN404, 'side' );
	add_meta_box(
		'learn-404-meta-box-query',
		__( 'Captured $wp_query', 'learn-404' ),
		'learn_404_meta_box_query',
		WPLEARN404,
		'normal',
		'core'
	);
	add_meta_box(
		'learn-404-meta-box-server',
		__( 'Captured $_SERVER Variables', 'learn-404' ),
		'learn_404_meta_box_server',
		WPLEARN404,
		'normal',
		'core'
	);
	add_meta_box(
		'learn-404-meta-box-details',
		__( 'Capture Details', 'learn-404' ),
		'learn_404_meta_box_details',
		WPLEARN404,
		'side',
		'core'
	);
}

function learn_404_meta_box_query( $post ){
	include apply_filters( 'learn-404/view/meta-box-query', 'view/meta-box-wp-query.php' );
}

function learn_404_meta_box_details( $post ){
	$sent_instant_email = get_post_meta( $post->ID, 'sent_instant_email', true );
	include apply_filters( 'learn-404/view/meta-box-details', 'view/meta-box-details.php' );
}

function learn_404_meta_box_server( $post ){
	$HTTP_REFERER = get_post_meta( $post->ID, 'HTTP_REFERER', true );
	$REQUEST_URI = get_post_meta( $post->ID, 'REQUEST_URI', true );
	$QUERY_STRING = get_post_meta( $post->ID, 'QUERY_STRING', true );
	$HTTP_USER_AGENT = get_post_meta( $post->ID, 'HTTP_USER_AGENT', true );
	include apply_filters( 'learn-404/view/meta-box-server', 'view/meta-box-server.php' );
}

function learn_404_checker() {
	global $wp_query, $wpdb;

	if ($wp_query-> is_404()) {

		$post_404_args = array(
			'post_title'     => $_SERVER['REQUEST_URI'], 
			'post_content'   => json_encode($wp_query), 
			'post_type'      => WPLEARN404,
			'post_status'    => 'pending', 
			'ping_status'    => 'closed',
			'comment_status' => 'closed'
			);

		$post_404_id = wp_insert_post( $post_404_args );

		if ( !is_wp_error( $post_404_id ) ) {

			foreach( array_keys( $_SERVER ) as $server_key ){
				add_post_meta( $post_404_id, $server_key, $_SERVER[ $server_key ], true );	
			}

			$email_to = apply_filters( 'learn-404/instant/email_to', get_bloginfo( 'admin_email' ) );
			$email_subject = apply_filters( 'learn-404/instant/email_subject', get_bloginfo( 'name' ) . __( ': 404 detected', 'learn-404' ) );
			ob_start();
			include apply_filters( 'learn-404/view/email-instant', 'view/email-instant.php' );
			$email_message = ob_get_clean();
			$email_message = apply_filters( 'learn-404/instant/email_message', $email_message );
			if( wp_mail( $email_to, $email_subject, $email_message ) ){
				add_post_meta( $post_404_id, 'sent_instant_email', 1, true );
			}

		}
	}
}
add_action( 'init', 'learn_404_init' );
add_action( 'add_meta_boxes', 'learn_404_add_meta_box' );
add_filter( 'template_redirect', 'learn_404_checker', 1000 );
