<?php
/*
Plugin Name: Plugin-readme Parser
Plugin URI: https://pandammonium.org/wordpress/wordpress-dev/plugin-readme-parser/
Description: Convert a WordPress plugin's readme file to XHTML for embedding in a post or page.
Version: 2.0.0
Author: <a href="https://pandammonium.org/">Caity Ross</a>, <a href="https://artiss.blog">David Artiss</a>
Text Domain: plugin-readme-parser
*/

/**
* Plugin-readme Parser
*
* Main code - include various functions
*
* @package	Pandammonium-Readme-Parser
*/

define( 'pandammonium_readme_parser_version', '2.0.0' );

/**
* Main Includes
*
* Include all the plugin's functions
*
* @since	1.2
*/

$functions_dir = plugin_dir_path( __FILE__ ) . 'includes/';

// Include all the various functions

if ( is_admin() ) {

    include_once( $functions_dir . 'admin-config.php' );			// Various admin. functions

}

// This converts the markdown to XHTML, although I'm not convinced it can't be HTML5:
include_once( $functions_dir . 'Michelf/MarkdownExtra.inc.php' );		// PHP Markdown Extra

include_once( $functions_dir . 'functions.php' );					// Various functions

include_once( $functions_dir . 'generate-output.php' );				// Generate the output
?>
