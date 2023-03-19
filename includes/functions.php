<?php
/**
 * Helper functions.
 *
 * Functions called by main output generator.
 *
 * @package Pandammonium-ReadmeParser-Functions
 * @author dartiss, pandammonium
 * @since 1.2
 * @since 2.0.0 Wraps each function definition inside a check for its
 * existence. Moves some functions into Generate_Output. Enhances the
 * documentation to meet the PHPDoc specification. Removes code to do with
 * obtaining images from the WordPress SVN server, to which access from the
 * plugin is forbidden. Improves error handling by using exceptions and
 * WP_Error objects.
 *
 * @todo Sort out the calls to prp_log().
 * @todo Improve the documentation wrt PHPDoc.
 * @todo Consider converting to a class.
 */

// If this file is called directly, abort:
defined( 'ABSPATH' ) or die();
defined( 'WPINC' ) or die();

if ( !defined( 'WP_PLUGIN_DIR_URL' ) ) {
  /**
   * @const string The base URL for the WordPress SVN server for plugins.
   *
   * @author pandammonium
   * @since 2.0.0
   */
  define( 'WP_PLUGIN_DIR_URL', 'https://plugins.svn.wordpress.org/' );
}
if ( !defined( 'WP_USER_DIR_URL' ) ) {
  /**
   * @const string The base URL for WordPress user profiles.
   *
   * @author pandammonium
   * @since 2.0.0
   */
  define( 'WP_USER_DIR_URL', 'https://profiles.wordpress.org/users/' );
}
if ( !defined( 'WP_PLUGIN_TAGS_URL' ) ) {
  /**
   * @const string The base URL for WordPress plugin tags.
   *
   * @author pandammonium
   * @since 2.0.0
   */
  define( 'WP_PLUGIN_TAGS_URL', 'https://wordpress.org/extend/plugins/tags/' );
}


if ( !function_exists( 'prp_log' ) ) {
  /**
   * Prints a message to the debug log file or to the web page.
   *
   * The message is sent to the error log if the WordPress debug constants
   * (defined in wp-config.php) permit it; likewise to the display. Errors are
   * displayed if the function is instructed to echo the output.
   *
   * @author pandammonium
   * @since 2.0.0
   *
   * @uses prp_print_debug_status()
   * @uses prp_get_wp_error_string()
   *
   * @param string message_name  A name to associate with the message. This is
   * useful if logging multiple messages.
   * @param mixed $message The message to be logged.
   * @param bool $error Whether the message is about an error or not. The
   * default is false, the message is not about an error. True if the message
   * is about an error.
   * @param bool $echo Forces the message name and message to be displayed on
   * the web page; overrides WP_DEBUG_DISPLAY. The default is false, the
   * message name and message will not be displayed on the web page.
   * @throws none
   * @return string The fully constructed and formatted message.
   */
  function prp_log( string $message_name, mixed $message = '', bool $error = false, bool $echo = false ): string {

    $debugging = defined( 'WP_DEBUG' ) && WP_DEBUG;
    $debug_logfile = defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG;
    $debug_display = defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY;

    prp_print_debug_status( $debugging, $debug_logfile, $debug_display, $error, $echo );

    $header = ( '' === $message_name ) ? '' : $message_name;
    $divider = ( '' === $message ) ? '' : ': ';
    $message_type = gettype( $message );
    $output = '';
    switch ( $message_type ) {
      case 'integer':
        $output = print_r( $header . $divider . var_export( strval( $message ),true ), true );
        break;
      case 'string':
        $output = $message ? '\'' . trim( $message ) . '\'' : '';
        $output = print_r( $header . $divider . $output, true );
      break;
      case 'object':
        if ( is_wp_error( $message ) ) {
          $output = prp_get_wp_error_string( $message, $echo );
          break;
        } else {
          // Fall through
        }
      default:
        $output = print_r( $header . $divider, true ) . var_export( $message, true );
      break;
    }

    if ( $error ) {
      $error_label = 'ERROR';
      if ( false === stripos( $output, 'error' ) ) {
        $output = $error_label . ' ' . $output;
      } else {
        // Make sure the error label is upper case
        $output = str_ireplace( $error_label, strtoupper($error_label), $output );
      }
    }

    $prefix = ( strncmp( $output, plugin_readme_parser_name, strlen( plugin_readme_parser_name ) ) === 0 ) ? '' : 'PRP | ';

    if ( ( $debugging && $debug_logfile ) ||
         ( $error && !$echo ) ) {
      $output = str_ireplace( '&lt;', '<', $output );
      $output = str_ireplace( '&gt;', '>', $output );
      error_log( $prefix . wp_strip_all_tags( trim( $output ) ) );
    }

    if ( ( $debugging && $debug_display ) ||
         ( $error && $echo ) ||
         ( $echo ) ) {

      $delim = ':';
      $pos = strpos( $output, $delim );
      if ( false !== $pos ) {
        $output = '<b>' . str_replace( $delim, $delim . '</b>', $output );
      }
      switch ( $message_type ) {
        case 'string':
        case 'integer':
          $output = '<p>' . $output . '</p>';
        break;
        case 'object':
          if ( is_wp_error( $message ) ) {
            // Do nothing: output was formatted in 'prp_get_wp_error_string'.
            break;
          } else {
            // Fall through
          }
        default:
          $output = '<pre>' . $output . '</pre>';
        break;
      }
    }
    return $output;
  }
}

if ( !function_exists( 'prp_get_wp_error_string' ) ) {
  /**
   * Returns a pretty-printed string from a WP_Error object.
   *
   * Returns a string containing the error's code, message and any data it may
   * have. The string is formatted as HTML, if required.
   *
   * @author pandammonium
   * @since 2.0.0
   *
   * @param WP_Error $error The error object to get the error information from.
   * @param bool $html True to format the output as HTML; false (default) to
   * omit HTML formatting.
   * @throws PRP_Exception if the wrong type of object was provided.
   * @return string A pretty-printed string from the WP_Error.
   */
  function prp_get_wp_error_string( WP_Error $error, bool $html = false ): string {

    if ( is_wp_error( $error ) ) {
      $output = plugin_readme_parser_name .
        ' error ' .
        trim( print_r( $error->get_error_code(), true ) ).
        ': ' .
        trim( print_r( $error->get_error_message(), true ) ) .
        ( empty( $error->get_error_data() ) ? '' : '. \'' . trim( print_r( $error->get_error_data(), true ) ) . '\'' );

      return $html ? '<p>' . $output . '.</p>' : $output;
    } else {
      throw new PRP_Exception( 'Expected a WP_Error object; got a ' . gettype( $error ), PRP_Exception::PRP_ERROR_BAD_DATA );
    }
  }
}

if ( !function_exists( 'prp_log_truncated_line' ) ) {
  /**
   * Truncates an error log line to a fixed number of characters.
   *
   * For debug use only.
   *
   * @author pandammonium
   * @since 2.0.0
   *
   * @param string $line The line of text to be truncated.
   * @param $line_number The line number, if there is one (e.g. the $line is
   * taken from a file).
   * @throws none
   * @return void
   */
  function prp_log_truncated_line( string $line, int $line_number = -1 ): void {

    // prp_log( 'function', __FUNCTION__ );
    // prp_log( 'arguments', func_get_args() );

    $line_length = 46;

    if ( $line_number > -1 ) {
      $line = 'l.' . $line_number . ' ' . $line;
    }
    $line = substr( $line, 0, $line_length ) . ( strlen( $line ) > $line_length ? '…' : '' );

    // prp_log( __( $line, plugin_readme_parser_domain ) );
  }
}

if( !function_exists( 'prp_print_debug_status' ) ) {
  /**
   * Prints the debug status.
   *
   * Prints the debug status to the display, if $echo is true. Always prints to
   * the error_log. Printing to each is subject to the WordPress settings in
   * wp-config.php.
   *
   * For debug use only; may only be called from inside prp_log();
   *
   * @author pandammonium
   * @since 2.0.0
   *
   * @param bool $debugging True if WP_DEBUG is true, otherwise false.
   * @param bool $ebug_logfile True if WP_DEBUG_LOG is true, otherwise false.
   * @param bool $debug_display True if WP_DEBUG_DISPLAY is true, otherwise
   * false.
   * @param bool $error True if an error is being logged; otherwise false.
   * @param bool $echo True if the message is being logged to the display;
   * otherwise false. Also controls whether the output of this function is sent
   * to the display or nly to the error log.
   * @throws none
   * @return void
   */
  function prp_print_debug_status( bool $debugging, bool $debug_logfile, bool $debug_display, bool $error, bool $echo = false ) {

    $trace = debug_backtrace();
    if ( $trace[ 1 ][ 'function' ] !== 'prp_log' ) {
      throw new PRP_Exception( __FUNCTION__ . '() must be called from inside prp_log()', E_USER_WARNING );
    }

    if ( $echo ) {
      echo '<pre>' .
        print_r( 'WP_DEBUG:         ' . ($debugging ? 'true' : 'false' ), true ) . '<br>' .
        print_r( 'WP_DEBUG_LOG:     ' . ($debug_logfile ? 'true' : 'false' ), true ) . '<br>' .
        print_r( 'WP_DEBUG_DISPLAY: ' . ($debug_display ? 'true' : 'false' ), true ) . '<br><br>' .
        print_r( '$error:           ' . ($error ? 'true' : 'false' ), true ) . '<br>' .
        print_r( '$echo:            ' . ($echo ? 'true' : 'false' ), true ) .
     '</pre>' . '<hr>';
    }
    error_log( print_r( '  WP_DEBUG:         ' . ($debugging ? 'true' : 'false' ), true ) );
    error_log( print_r( '  WP_DEBUG_LOG:     ' . ($debug_logfile ? 'true' : 'false' ), true ) );
    error_log( print_r( '  WP_DEBUG_DISPLAY: ' . ($debug_display ? 'true' : 'false' ), true ) );
    error_log( print_r( '  error:            ' . ($error ? 'true' : 'false' ), true ) );
    error_log( print_r( '  echo:             ' . ($echo ? 'true' : 'false' ), true ) );

  }
}

if ( !function_exists( 'prp_check_img_exists' ) ) {
  /**
   * Checked the image existed on the WordPress SVN server.
   *
   * @author dartiss, pandammonium
   * @since 1.2
   * @deprecated 2.0.0 This function is obsolete because it resulted in an HTTP
   * 403 (Forbidden) error from the WordPress SVN server. It currently returns
   * an empty string, and will be removed from future versions of this plugin.
   *
   * @param string $filename The filename minus its extension.
   * @param string $ext The file extension, including the dot.
   * @throws E_USER_DEPRECATED to indicate that this function is deprecated.
   * @return false
   */
  function prp_check_img_exists( string $filename, string $ext ): false {
    trigger_error( 'It is not possible to determine whether image files exist on the WordPress SVN server or not, therefore ' . __FUNCTION__ . '() is deprecated and has no effect on the output', E_USER_DEPRECATED );
    return false;
  }
}

if ( !function_exists( 'prp_toggle_global_shortcodes' ) ) {
  /**
   * Toggle the shortcodes so that any shortcodes in the readme file aren't
   * expanded.
   *
   * Expanded shortcodes in the readme files cause problems if they're used to
   * provide examples of use of the plugin's shortcode.
   *
   * Some plugins change this filter’s priority, so clear the global list of
   * registered shortcodes temporarily, except for this plugin's readme_info,
   * which is needed.
   *
   * @author pandammonium
   * @since 2.0.0
   * @link https://wordpress.stackexchange.com/a/115176 Stack Exchange was used
   * to inform the implementation of this function.
   *
   * @uses PRP_Exception
   *
   * @param string $content The readme file content.
   * @param string $exceptions The shortcodes to keep active.
   * @throws none
   * @return string The readme file content or an error message.
   */
  function prp_toggle_global_shortcodes( string $content ): string {

    // prp_log( 'function', __FUNCTION__ );
    // prp_log( 'arguments', func_get_args() );

    static $original_shortcodes = array();
    $file = plugin_dir_path( __DIR__ );
    // prp_log( __( 'Plugin directory: ', plugin_readme_parser_domain ) . $file );
    if ( str_contains( $file, plugin_readme_parser_filename ) ) {
      // prp_log( __( 'no. original shortcodes', plugin_readme_parser_domain ), count ( $original_shortcodes ) );
      // prp_log( __( 'no. global shortcodes', plugin_readme_parser_domain ), count ( $GLOBALS['shortcode_tags'] ) );
      // prp_log( __( 'shortcode content', plugin_readme_parser_domain ), $content );

      if ( count ( $original_shortcodes ) === 0 ) {
        // Toggle the shortcodes OFF

        $original_shortcodes = $GLOBALS['shortcode_tags'];
        $GLOBALS['shortcode_tags'] = array();

        $current_theme_supports_blocks = wp_is_block_theme();

        if ( $current_theme_supports_blocks ) {
          // prp_log( __( 'this theme DOES support blocks', plugin_readme_parser_domain ) );
          //   prp_log( __( 'Toggling ALL global shortcodes OFF', plugin_readme_parser_domain ) );
          if  ( str_contains( $content, '[readme_info' ) ) {
            // prp_log( __( 'content contains \'[readme_info\'', plugin_readme_parser_domain ) );
            // $GLOBALS['shortcode_tags']['readme_info'] = 'readme_info';
            // prp_log( __( 'toggling global shortcodes OFF except for:', plugin_readme_parser_domain ) );
            // prp_log( 'global shortcodes', $GLOBALS['shortcode_tags'] );
          }

        } else {
          // prp_log( __( 'this theme DOES NOT support blocks', plugin_readme_parser_domain ) );

          // Need to put some of this plugin's ones back, otherwise it all breaks; it's unclear as to why and as to why these combinations work:

          if ( ( str_contains( $content, '[readme ' ) ) ||
               ( str_contains( $content, '[readme]' ) ) ) {
            // prp_log( __( 'content contains \'[readme \' or \'[readme]\'', plugin_readme_parser_domain ) );

            $GLOBALS['shortcode_tags']['readme'] = 'readme_parser';
            $GLOBALS['shortcode_tags']['readme_info'] = 'readme_info';

          } else if  ( str_contains( $content, '[readme_info' ) ) {
            // prp_log( __( 'content contains \'[readme_info\'', plugin_readme_parser_domain ) );

            $GLOBALS['shortcode_tags']['readme_info'] = 'readme_info';

          } else {
            // prp_log( __( 'failed to find ' . plugin_readme_parser_name . ' shortcode', plugin_readme_parser_domain ) );

            // We're in the wild, not writing out a readme with this plugin, so all the shortcodes need to be functional:
            // prp_log( __( 'toggling ALL global shortcodes ON', plugin_readme_parser_domain ) );
            // prp_log( __( 'no. original shortcodes', plugin_readme_parser_domain ) . count ( $original_shortcodes ) );
            // prp_log( __( 'no. global shortcodes' . count ( $GLOBALS['shortcode_tags'] ), plugin_readme_parser_domain ) );
            $GLOBALS['shortcode_tags'] = $original_shortcodes;

          }

          // prp_log( __( 'toggling global shortcodes OFF except for:', plugin_readme_parser_domain ) );
          // prp_log( 'global shortcodes', $GLOBALS['shortcode_tags'] );
        }

      } else {
        // Toggle the shortcodes ON

        // prp_log( __( 'toggling global shortcodes ON', plugin_readme_parser_domain ) );

        $GLOBALS['shortcode_tags'] = $original_shortcodes;
        $original_shortcodes = array();
        // prp_log( __( 'repopulating GLOBAL shortcodes with original shortcodes', plugin_readme_parser_domain ) );

      }
    } else {
      // Can't throw an exception here because it won't be caught by the plugin, presumably because it's used as a filter on `the_content`. Use WP_Error instead:
      $error = new WP_Error();
      $error->add( PRP_Exception::PRP_ERROR_BAD_INPUT, 'Wrong plugin. Expected <samp><kbd>' . plugin_readme_parser_domain . '</kbd></samp>; got <samp><kbd>' . basename( $file ) . '</kbd></samp>' );
      // prp_log( 'has errors', $error->has_errors() );
      // prp_log( 'error', $error );
      // prp_log( 'error code', $error->get_error_code() );
      // prp_log( 'error message', $error->get_error_message() );

      // Turn all the shortcodes on:
      $GLOBALS['shortcode_tags'] = $original_shortcodes;
      $original_shortcodes = array();

      return prp_log( 'error', $error, true, true );
      // return $error;
    }
    return $content;
  }

  add_filter( 'the_content', 'prp_toggle_global_shortcodes', -1 );
  add_filter( 'the_content', 'prp_toggle_global_shortcodes', PHP_INT_MAX );
}
?>
