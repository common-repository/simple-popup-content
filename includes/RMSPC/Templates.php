<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Template Loader
 *
 * Call Stack
 *RMSPC__Main 
  	-> plugins_loaded() -- hooked to plugins_loaded
  		-> addHooks
  			-> RMSPC__Templates::init -- hooked to plugins_loaded
  			
 * @class 		RMSPC__Templates
 * @version		1.0
 * @category	Class
 * @author 		David Richied
 */
class RMSPC__Templates {

	/**
	 * @var bool Is wp_head complete?
	 */
	public static $wpHeadComplete = false;

		/**
		 * The template name currently being used
		 */
		protected static $template = false;

	/**
	 * Hook in methods.
	 */
	public static function init() {


		// Choose the wordpress theme template to use
		add_filter( 'template_include', array( __CLASS__, 'templateChooser' ) );
		//add_filter( 'template_include', array( __CLASS__, 'template_loader' ) );
		add_action( 'wp_head', array( __CLASS__, 'wpHeadFinished' ), 999 );

		// make sure we enter the loop by always having some posts in $wp_query
		//add_action( 'wp_head', array( __CLASS__, 'maybeSpoofQuery' ), 100 );
	}


	/**
	 * Determine when wp_head has been triggered.
	 */
	public static function wpHeadFinished() {
		self::$wpHeadComplete = true;
	}


	/**
	 * Check to see if this is operating in the main loop
	 *
	 * @param WP_Query $query
	 *
	 * @return bool
	 */
	protected static function is_main_loop( $query ) {
		return $query->is_main_query();
	}



	/**
	 * Load a template.
	 *
	 * Handles template usage so that we can use our own templates instead of the themes.
	 *
	 * @param mixed $template
	 * @return string
	 */
	public static function templateChooser( $template ) {

		$popup_id = RMSPC__Templates::get_popup_id(get_the_id());
		// exit if this post does not have a popup attached to it (we do not touch the content)
		if ( ! $popup_id ) {
			//var_dump("tec tempaltechooset");
			return $template;
		}



		$resources_url = plugins_url( '/simple-popup-content/' );

		wp_enqueue_style( 'vex-css', $resources_url . 'assets/css/vex.css' );
		wp_enqueue_style( 'vex-theme-css', $resources_url . 'assets/css/vex-theme-wireframe.css' );
		wp_enqueue_style( 'spc-css', $resources_url . 'assets/css/simple-popup-content.css' );

		// //enqueue JavaScript
		// wp_enqueue_script( 'jquery' );

		wp_register_script (
			'vex-modals',
			 $resources_url . 'assets/js/vex.combined.min.js',
			array( 'jquery' ),
			'',
			true
		);
	  wp_enqueue_script('vex-modals');

		wp_register_script (
			'rm-simple-popup-content-js', 
			$resources_url . 'assets/js/simple-popup-content.js',
			array( 'jquery' ),
			'',
			true
		);
	  wp_enqueue_script('rm-simple-popup-content-js');

		wp_localize_script( 'rm-simple-popup-content-js', 'spc_ajax', array(
			'ajaxurl' 		=> admin_url( 'admin-ajax.php' ),
			'popup_post_id' => $popup_id,
		));




		add_action( 'loop_start', array( __CLASS__, 'setup_ecp_template' ) );
        
  	$template =  locate_template('page.php');
      
		self::$template = $template;

		return $template;

	}


		/**
		 * This is where the magic happens where we run some ninja code that hooks the query to resolve to an events template.
		 *
		 * @param WP_Query $query
		 */
		public static function setup_ecp_template( $query ) {
			
			//do_action( 'tribe_events_filter_the_page_title' );

			if ( self::is_main_loop( $query ) && self::$wpHeadComplete ) {

				// on loop start, unset the global post so that template tags don't work before the_content()
				//add_action( 'the_post', array( __CLASS__, 'spoof_the_post' ) );


				// on the_content, load our events template
				add_filter( 'the_content', array( __CLASS__, 'load_ecp_into_page_template' ) );

				//// remove the comments template
				//add_filter( 'comments_template', array( __CLASS__, 'load_ecp_comments_page_template' ) );

				// only do this once
				remove_action( 'loop_start', array( __CLASS__, 'setup_ecp_template' ) );
			}
		}

		/**
		 * Spoof the query so that we can operate independently of what has been queried.
		 *
		 * @return object
		 */
		private static function spoofed_post() {
			$spoofed_post = array(
				'ID'                    => 0,
				'post_status'           => 'draft',
				'post_author'           => 0,
				'post_parent'           => 0,
				'post_type'             => 'page',
				'post_date'             => 0,
				'post_date_gmt'         => 0,
				'post_modified'         => 0,
				'post_modified_gmt'     => 0,
				'post_content'          => '',
				'post_title'            => '',
				'post_excerpt'          => '',
				'post_content_filtered' => '',
				'post_mime_type'        => '',
				'post_password'         => '',
				'post_name'             => '',
				'guid'                  => '',
				'menu_order'            => 0,
				'pinged'                => '',
				'to_ping'               => '',
				'ping_status'           => '',
				'comment_status'        => 'closed',
				'comment_count'         => 0,
				'is_404'                => false,
				'is_page'               => false,
				'is_single'             => false,
				'is_archive'            => false,
				'is_tax'                => false,
			);

			return ( object ) $spoofed_post;
		}


	/**
	 * Spoof the global post just once
	 *
	 **/
	public static function spoof_the_post() {
		
		$GLOBALS['post'] = self::spoofed_post();

		remove_action( 'the_post', array( __CLASS__, 'spoof_the_post' ) );
	}



		/**
		 * Decide if we need to spoof the query.
		 */
		public static function maybeSpoofQuery() {

			global $wp_query;

			if ( $wp_query->is_main_query() ) {

				// we need to ensure that we always enter the loop, whether or not there are any events in the actual query

				$spoofed_post = self::spoofed_post();

				$GLOBALS['post']      = $spoofed_post;
				$wp_query->posts[]    = $spoofed_post;
				$wp_query->post_count = count( $wp_query->posts );

				$wp_query->spoofed = true;
				$wp_query->rewind_posts();

			}
		}

	/**
	 * Loads the contents into the page template
	 *
	 * @return string Page content
	 */
	public static function load_ecp_into_page_template( $contents  ) {
		// only run once!!!
		remove_filter( 'the_content', array( __CLASS__, 'load_ecp_into_page_template' ) );

		//self::restoreQuery();

		ob_start();

		//echo tribe_events_before_html();
		$popup_id = RMSPC__Templates::get_popup_id(get_the_id());
		// src > functions > template-tags > general.php
		rmspc_get_view($popup_id);

		//echo tribe_events_after_html();

		$popup_content = ob_get_clean();

		$contents .= $popup_content;

		// make sure the loop ends after our template is included
		self::endQuery();

		return $contents;
	}


	/**
	 * Figure out if this page has any popups
	 *
	 * @return string Page content
	 */
	public static function get_popup_id($post_id) {
		global $wpdb;

		$querystr = "
		  SELECT $wpdb->postmeta.post_id
		  FROM $wpdb->postmeta
		  WHERE $wpdb->postmeta.meta_value = '$post_id'
		  AND $wpdb->postmeta.meta_key = '_rmspc_teaser'
		";

		$popup_id = $wpdb->get_var($querystr);

		return $popup_id;

	}

	/**
	 * Query is complete: stop the loop from repeating.
	 */
	private static function endQuery() {
		global $wp_query;

		$wp_query->current_post = -1;
		$wp_query->post_count   = 0;
	}
	/**
	 * Restore the original query after spoofing it.
	 */
	public static function restoreQuery() {
		global $wp_query;

		// If the query hasn't been spoofed we need take no action
		if ( ! isset( $wp_query->spoofed ) || ! $wp_query->spoofed ) {
			return;
		}

		// Remove the spoof post and fix the post count
		array_pop( $wp_query->posts );
		$wp_query->post_count = count( $wp_query->posts );

		// If we have other posts besides the spoof, rewind and reset
		if ( $wp_query->post_count > 0 ) {
			$wp_query->rewind_posts();
			wp_reset_postdata();
		}
		// If there are no other posts, unset the $post property
		elseif ( 0 === $wp_query->post_count ) {
			$wp_query->current_post = -1;
			unset( $wp_query->post );
		}

		// Don't do this again
		unset( $wp_query->spoofed );
	}

}
