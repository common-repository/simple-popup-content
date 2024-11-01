<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link http://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */

// Returns 0 if user is not logged in
$user_id = get_current_user_id();

$post_id = get_the_ID();

$key = 'purchased_content';

$purchased_content = get_user_meta($user_id, $key, false);

$has_purchased = in_array($post_id, $purchased_content);

while ( have_posts() ) :  the_post();

	if ($has_purchased) {
		the_content();
	} else {
		$teaser_content = get_post_meta($post_id, '_rmspc_teaser');
		$teaser_content = !empty($teaser_content) ? $teaser_content[0] : 'Please fill out the form below to purchase this Simple Popup Content';
		include("payment-form.php");
		echo $teaser_content;
		echo  get_form();
	}

	
	
endwhile;