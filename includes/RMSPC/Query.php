<?php
/**
 * Controls the main premium post query.  Allows for recurring events.
 *Class Stack
 *RMSPC__Main 
  	-> plugins_loaded() -- hooked to plugins_loaded
  		-> addHooks
  			-> init() -- hooked to init
  				-> RMSPC__Query
  					-> init()
  						-> parse_query() -- hooked to parse_query

 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'RMSPC__Query' ) ) {
	class RMSPC__Query {
		/**
		 * Set any query flags
		 *
		 * @param WP_Query $query
		 **/


		// Check to see if this is a request for simple_popup_content post type post
		public static function init() {
			// if premium post query add filters
			add_action( 'parse_query', array( __CLASS__, 'parse_query' ), 50 );
		}

		public static function parse_query( $query ) {


			
			$types = ( ! empty( $query->query_vars['post_type'] ) ? (array) $query->query_vars['post_type'] : array() );
			// check if any possiblity of this being a premium post query
			$query->rmspc_is_event = ( in_array( RMSPC__Main::POSTTYPE, $types ) && count( $types ) < 2 )
				? true // it was an premium post query
				: false;

			$query->rmspc_is_event_query = ( $query->rmspc_is_event )
				? true // this is an event query of some type
				: false; // move along, this is not the query you are looking for
		}
	}
}