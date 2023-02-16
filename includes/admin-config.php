<?php
/**
* Admin Menu Functions
*
* Various functions relating to the various administration screens
*
* @package	Pandammonium-README-Parser
*/

/**
* Add meta to plugin details
*
* Add options to plugin meta line
*
* @since	1.2
*
* @param	string  $links	Current links
* @param	string  $file	File in use
* @return   string			Links, now with settings added
*/

function arp_set_plugin_meta( $links, $file ) {

	if ( strpos( $file, 'wp-readme-parser.php' ) !== false ) { $links = array_merge( $links, array( '<a href="https://wordpress.org/support/plugin/wp-readme-parser">' . __( 'Support','wp-readme-parser' ) . '</a>' ) ); }

	return $links;
}

add_filter( 'plugin_row_meta', 'arp_set_plugin_meta', 10, 2 );
?>
