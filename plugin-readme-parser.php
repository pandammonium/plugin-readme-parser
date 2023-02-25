<?php
/**
 * Plugin Name: Plugin-readme Parser
 * Plugin URI:  https://pandammonium.org/wordpress-dev/plugin-readme-parser/
 * Description: Converts a WordPress plugin's readme file to XHTML for embedding in a post or page.
 * Version:     2.0.0
 * Author:      Caity Ross
 * Author URI:  http://pandammonium.org/
 * Text Domain: plugin-readme-parser
 * @link        https://pandammonium.org/wordpress-dev/plugin-readme-parser/
 * @since       1.0
 * @package     Plugin_readme_Parse
 * @author      pandammonium, dartiss
 * @license     GPLv2 or later
 * @wordpress-plugin
 */

/**
* Plugin-readme Parser
*
* Main code - include various functions
*
* @package	Pandammonium-Readme-Parser
*/

define( 'pandammonium_readme_parser_version', '2.0.0' );
define( 'pandammonium_readme_parser_filename', 'plugin-readme-parser' );
define( 'pandammonium_readme_parser_name', 'Plugin-readme Parser' );

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
