<?php
/**
 * Plugin Name: Plugin-readme Parser
 * Plugin URI:  https://pandammonium.org/wordpress-dev/
 * plugin-readme-parser/
 * Description: Converts a WordPress plugin's readme file to XHTML
 * for embedding in a post or page.
 * Version:     2.0.0
 * Author:      Caity Ross
 * Author URI:  https://pandammonium.org/
 * Text Domain: plugin-readme-parser
 * @link        https://pandammonium.org/wordpress-dev/plugin-readme-parser/
 * @since       1.0
 * @package     Pandammonium-ReadmeParser
 * @author      pandammonium, dartiss
 * @license     GPLv2 or later
 * @wordpress-plugin
 */

// If this file is called directly, abort:
defined( 'ABSPATH' ) or die();
defined( 'WPINC' ) or die();


define( 'plugin_readme_parser_version', '2.0.0' );
define( 'plugin_readme_parser_filename', 'plugin-readme-parser' );
define( 'plugin_readme_parser_domain', plugin_readme_parser_filename );
define( 'plugin_readme_parser_name', 'Plugin-readme Parser' );

/**
* Main Includes
*
* Include all the plugin's functions
*
* @since	1.2
*/

$functions_dir = plugin_dir_path( __FILE__ ) . 'includes/';
$classes_dir = plugin_dir_path( __FILE__ ) . 'classes/';

// Include all the various functions

if ( is_admin() ) {

  include_once( $functions_dir . 'admin-config.php' );			// Various admin. functions

}

require_once( $classes_dir . 'exception_prp.php' );


// This converts the markdown to XHTML, although I'm not convinced it can't be HTML5:
include_once( $functions_dir . 'Michelf/MarkdownExtra.inc.php' );		// PHP Markdown Extra

include_once( $functions_dir . 'functions.php' );					// Various functions

require_once( $classes_dir . 'generate-output.php' );
?>
