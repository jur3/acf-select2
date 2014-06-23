<?php

/*
Plugin Name: Advanced Custom Fields: Select2
Plugin URI: PLUGIN_URL
Description: DESCRIPTION
Version: 1.0.0
Author: Jure Suvak
Author URI: http://dlabs.si
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/




// 1. set text domain
// Reference: https://codex.wordpress.org/Function_Reference/load_plugin_textdomain
load_plugin_textdomain( 'acf-select2', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );




// 2. Include field type for ACF5
// $version = 5 and can be ignored until ACF6 exists
function include_field_types_select2( $version ) {
	
	include_once('acf-select2-v5.php');
	
}

add_action('acf/include_field_types', 'include_field_types_select2');




// 3. Include field type for ACF4
function register_fields_select2() {
	
	include_once('acf-select2-v4.php');
	
}

add_action('acf/register_fields', 'register_fields_select2');



	
?>