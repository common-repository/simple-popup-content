<?php
// Get contents of popup
//Functions
	//rmspc_get_view() - display simple_popup_content CPT

//Call Stack
//RMSPC__Templates
	//load_ecp_into_page_template
		//rmspc_get_view
if ( class_exists( 'RMSPC__Main' ) ) {

	/**
	 * Includes a view file, runs hooks around the view
	 *
	 * @param bool|string $view View slug
	 *
	 **/
	function rmspc_get_view( $popup_id ) {



		$the_popup = get_post($popup_id);
		$content = $the_popup->post_content;
    //$content = apply_filters ("the_content", $the_popup->post_content);
    $content = wpautop($content);
    //$content = shortcode_unautop($content);

    $popup_content = '<div style="display:none"><div id="simple-popup-content">';
    $popup_content .= $content;
    $popup_content .= '</div></div>';
		echo $popup_content;

		$resources_url = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/simple-popup-content/';
		$template_file =  $resources_url . 'src/views/single-event.php';

		if ( file_exists( $template_file ) ) {
			include( $template_file );
		}
	}
}