<?php
/*
Plugin Name: Post and Page Asides
Description: This plugin (in conjunction with the Mayflower theme) allows users to add asides to posts and pages.
Plugin URI: https://github.com/BellevueCollege/post-and-page-asides/
Author: Bellevue College Information Technology Services
Version: 0.0.0.1
Author URI: http://www.bellevuecollege.edu
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Return Aside Title
 */
function post_and_page_asides_return_title() {
	return get_post_meta( get_the_ID(), 'ppa_aside_title', true );
}

/**
 * Return Aside Content through Content Filters
 */
function post_and_page_asides_return_content() {
	$text = get_post_meta( get_the_ID(), 'ppa_aside_text', true );
	$text = apply_filters( 'the_content', $text );
	$text = str_replace( ']]>', ']]&gt;', $text );
	return $text;
}

/**
 * Return Type
 */
function post_and_page_asides_return_type() {
	return get_post_meta( get_the_ID(), 'ppa_aside_type', true );
}

/**
 * Load Classes
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-post-and-page-asides.php';

/**
 * Build Plugin
 */
function call_Post_And_Page_Asides() {
	new Post_And_Page_Asides();
}

/**
 * Call Plugin on dashboard pages
 */
if ( is_admin() ) {
	add_action( 'load-post.php', 'call_Post_And_Page_Asides' );
	add_action( 'load-post-new.php', 'call_Post_And_Page_Asides' );
}

