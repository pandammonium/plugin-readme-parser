<?php
/**
 * Admin Menu Functions
 *
 * Various functions relating to the various administration screens
 *
 * @package Pandammonium-Readme-Parser
 */

/**
 * Add meta to plugin details
 *
 * Add options to plugin meta line
 *
 * @since 1.2
 *
 * @param  string  $links Current links
 * @param  string  $file  File in use
 * @return string  Links, now with settings added
 */

if (!function_exists('prp_add_settings_link')) {
/**
  * Add a link to the Plugin-readme Parser settings page from the installed
  * plugins list.
  *
  * @since 2.0.0
  */
  function prp_add_settings_link( $links, $file ) {

    if ( strpos( $file, 'plugin-readme-parser.php' ) !== false ) {

      $links = array_merge( $links, array( '<a href="' . esc_url( admin_url( 'options-general.php?page=plugin-readme-parser' ) ) . '">' . __('Settings', 'plugin-readme-parser') . '</a>' ) );
    }

    return $links;
  }
  // add_filter( 'plugin_action_links', 'prp_add_settings_link', 10, 2 );
}


function prp_set_plugin_meta( $links, $file ) {

  if ( strpos( $file, 'plugin-readme-parser.php' ) !== false ) {
    $links = array_merge( $links, array( '<a href="https://wordpress.org/support/plugin/plugin-readme-parser">' . __( 'Support','plugin-readme-parser' ) . '</a>' ) );
  }

  return $links;
}

add_filter( 'plugin_row_meta', 'prp_set_plugin_meta', 10, 2 );
?>
