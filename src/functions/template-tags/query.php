<?php
//Functions
	//tribe_is_event_query() - are we querying an simple_popup_content CPT?

/*
 *Call Stack
 *RMSPC__Main 
  	-> plugins_loaded() -- hooked to plugins_loaded
  		-> loadLibraries() includes query.php
*/
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( class_exists( 'RMSPC__Main' ) ) {
	/**
	 * Conditional tag to check if current page is displaying event query
	 *
	 * @return bool
	 **/
	function rmspc_is_event_query() {
		global $wp_query;
		$rmspc_is_event_query = ! empty( $wp_query->rmspc_is_event_query );

		return apply_filters( 'rmspc_query_is_event_query', $rmspc_is_event_query );
	}
}