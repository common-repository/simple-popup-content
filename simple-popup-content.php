<?php
/*
Plugin Name: Simple Popup Content
Plugin URI: https://richiedmedia.com
Description: Create a popup and choose which page to display it on using a dropdown list.
Version: 1.3
Author: David Richied
Author URI: https://richiedmedia.com
Text Domain: popup_page
Domain Path: /lang/
License: GPL2
*/

// Get post (src > functions > template-tags > general.php)
require_once 'includes/RMSPC/Main.php';

RMSPC__Main::instance();



function rm_spc_preload_popups() {
	global $query;
	$query = new WP_Query( array(
		'post_type'      => 'simple_popup_content',
		'posts_per_page' => - 1
	) );

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) : $query->next_post();
				do_action( 'rm_spc_preload_popup', $query->post->ID );
		endwhile;

	}
}

add_action( 'wp_enqueue_scripts', 'rm_spc_preload_popups', 11 );

function rm_spc_enqueue_gravityforms_during_preload( $popup_id ) {

	if ( function_exists( 'gravity_form_enqueue_scripts' ) ) {
		$regex = "/\[gravityform.*id=[\'\"]?([0-9]*)[\'\"]?.*/";
		$popup = get_post( $popup_id );
		preg_match_all( $regex, $popup->post_content, $matches );
		foreach ( $matches[1] as $form_id ) {
			add_filter( "gform_confirmation_anchor_{$form_id}", create_function( "", "return false;" ) );
			gravity_form_enqueue_scripts( $form_id, true );
		}
	}
}

add_action( 'rm_spc_preload_popup', 'rm_spc_enqueue_gravityforms_during_preload' );
