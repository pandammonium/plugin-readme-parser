<?php
/*
Plugin Name: Plugin README Parser
Plugin URI: https://wordpress.org/plugins/parse-plugin-readme/
Description: Convert a WordPress plugin's README file to XHTML for embedding in a post or page.
Version: 2.0.0
Author: Caity Ross, David Artiss
Author URI: https://pandammonium.org/, https://artiss.blog
Text Domain: plugin-readme-parser
*/

/**
* Plugin README Parser
*
* Main code - include various functions
*
* @package	Pandammonium-README-Parser
*/

define( 'pandammonium_readme_parser_version', '1.3.8' );

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

include_once( $functions_dir . 'Michelf/MarkdownExtra.inc.php' );		// PHP Markdown Extra

include_once( $functions_dir . 'functions.php' );					// Various functions

include_once( $functions_dir . 'generate-output.php' );				// Generate the output
?>
