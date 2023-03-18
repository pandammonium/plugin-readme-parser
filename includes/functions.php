<?php
/**
 * Helper functions.
 *
 * Functions called by main output generator.
 *
 * @package Pandammonium-Readme-Parser
 * @author dartiss, pandammonium
 * @since 1.2
 * @since 2.0.0 Wraps each function definition inside a check for
 * its existence. Moves some functions into Generate_Output.
 * Enhances the documentation to meet the PHPDoc specification.
 * Removes code to do with obtaining images from the WordPress SVN
 * server, to which access from the plugin is forbidden. Improves
 * error handling by using exceptions and WP_Error objects.
 *
 * @todo Add as many functions as is meaningful into the
 * Generate_Output class.
 * @todo Sort out the calls to prp_log().
 * @todo Improve the documentation wrt PHPDoc.
 */

// If this file is called directly, abort:
defined( 'ABSPATH' ) or die();
defined( 'WPINC' ) or die();

if ( !defined( 'WP_PLUGIN_DIR_URL' ) ) {
  /**
   * The base URL for the WordPress SVN server for plugins.
   *
   * @author pandammonium
   * @since 2.0.0
   */
  define( 'WP_PLUGIN_DIR_URL', 'https://plugins.svn.wordpress.org/' );
}
if ( !defined( 'WP_USER_DIR_URL' ) ) {
  /**
   * The base URL for WordPress user profiles.
   *
   * @author pandammonium
   * @since 2.0.0
   */
  define( 'WP_USER_DIR_URL', 'https://profiles.wordpress.org/users/' );
}
if ( !defined( 'WP_PLUGIN_TAGS_URL' ) ) {
  /**
   * The base URL for WordPress plugin tags.
   *
   * @author pandammonium
   * @since 2.0.0
   */
  define( 'WP_PLUGIN_TAGS_URL', 'https://wordpress.org/extend/plugins/tags/' );
}
if ( !defined( 'WP_PLUGIN_PAGE_URL' ) ) {
  /**
   * The base URL for official WordPress plugin pages.
   *
   * @author pandammonium
   * @since 2.0.0
   */
  define( 'WP_PLUGIN_PAGE_URL', 'https://wordpress.org/extend/plugins/' );
}
if ( !defined( 'WP_PLUGIN_SUPPORT_URL' ) ) {
  /**
   * The base URL for WordPress plugin support forums.
   *
   * @author pandammonium
   * @since 2.0.0
   */
  define( 'WP_PLUGIN_SUPPORT_URL', 'https://wordpress.org/support/plugin/' );
}

if ( !function_exists( 'prp_log' ) ) {
/**
 * Prints a message to the debug log file or to the web page.
 *
 * The message is sent to the error log if the WordPress debug
 * constants (defined in wp-config.php) permit it; likewise to the
 * display. Errors are displayed if the function is instructed to
 * echo the output.
 *
 * @author pandammonium
 * @since 2.0.0
 *
 * @param string message_name  A name to associate with the
 * message. This is useful if logging multiple messages.
 * @param mixed $message The message to be logged.
 * @param bool $error Whether the message is about an error or not.
 * Default is false, the message is not about an error. True if the
 * message is about an error.
 * @param bool $echo Forces the message name and message to be
 * displayed on the web page; overrides WP_DEBUG_DISPLAY. Default
 * is false, the message name and message will not be displayed on
 * the web page.
 * @return string The fully constructed and formatted message.
 */
  function prp_log( string $message_name, mixed $message = '', bool $error = false, bool $echo = false ): string {

    $debugging = defined( 'WP_DEBUG' ) && WP_DEBUG;
    $debug_logfile = defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG;
    $debug_display = defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY;

    // error_log( print_r( '  WP_DEBUG:         ' . ($debugging ? 'true' : 'false' ), true ) );
    // error_log( print_r( '  WP_DEBUG_LOG:     ' . ($debug_logfile ? 'true' : 'false' ), true ) );
    // error_log( print_r( '  WP_DEBUG_DISPLAY: ' . ($debug_display ? 'true' : 'false' ), true ) );
    // error_log( print_r( '  error:            ' . ($error ? 'true' : 'false' ), true ) );
    // error_log( print_r( '  echo:             ' . ($echo ? 'true' : 'false' ), true ) );

    // echo '<pre>' .
    //   print_r( 'WP_DEBUG:         ' . ($debugging ? 'true' : 'false' ), true ) . '<br>' .
    //   print_r( 'WP_DEBUG_LOG:     ' . ($debug_logfile ? 'true' : 'false' ), true ) . '<br>' .
    //   print_r( 'WP_DEBUG_DISPLAY: ' . ($debug_display ? 'true' : 'false' ), true ) . '<hr>' .
    //   print_r( '$error:           ' . ($error ? 'true' : 'false' ), true ) . '<br>' .
    //   print_r( '$echo:            ' . ($echo ? 'true' : 'false' ), true ) .
    //  '</pre>';

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
   * Returns a string containing the error's code, message and any
   * data it may have. The string is formatted as HTML, if required.
   *
   * @author pandammonium
   * @since 2.0.0
   *
   * @param WP_Error $error The error object to get the error
   * information from.
   * @param bool $html True to format the output as HTML; false
   * (default) to omit HTML formatting.
   * @throws PRP_Exception if the wrong type of object was
   * provided.
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
   * @param The $line_number line number, if there is one (e.g. the $line is taken from a file).
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

if ( !function_exists( 'prp_is_it_excluded' ) ) {
  /**
   * Tests whether the current section is explicitly excluded from
   * the display.
   *
   * The section heading is compared to the value of the 'exclude'
   * parameter of the shortcode to see if it has been explicitly
   * excluded. Screenshots are always excluded because the plugin
   * is forbidden from accessing the image files on the WordPress
   * SVN server where they are stored.
   *
   * @author dartiss
   * @since 1.0
   *
   * @param string $tofind Section name.
   * @param string $exclude List of excluded sections.
   * @return bool True or false, depending on whether the section
   * was valid or invalid.
   */
  function prp_is_it_excluded( string $tofind, string $exclude ): bool {

    // prp_log( 'function', __FUNCTION__ );
    // prp_log( 'arguments', func_get_args() );

    $tofind = strtolower( $tofind );
    $exclude = strtolower( $exclude );
    $return = true;

    // prp_log( __( '  Is \'' . $tofind . '\' excluded?', plugin_readme_parser_domain ) );
    // prp_log( __( '  exclusion list: \'' . $exclude . '\'', plugin_readme_parser_domain ) );

    if ( 'screenshots' === $tofind ||
         'screenshot' === $tofind ) {
      $return = true;

    } else {
      if ( $tofind !== $exclude ) {
        // Search in the middle
        $pos = strpos( $exclude, ',' . $tofind . ',' );
        if ( $pos === false ) {
          // Search on the left
          $pos = strpos( substr( $exclude, 0, strlen( $tofind ) + 1 ), $tofind . ',' );
          if ( $pos === false ) {
            // Search on the right
            $pos = strpos( substr( $exclude, ( strlen( $tofind ) + 1 ) * -1, strlen( $tofind ) + 1 ), ',' . $tofind );
            if ( $pos === false ) {
              $return = false;
            }
          }
        }
      }
    }
    // if ( 'description' === $tofind ) {
    //   prp_log( __( '  \'' . $tofind . '\' is ' . ( $return ? 'explicitly excluded' : 'not explicitly excluded' ), plugin_readme_parser_domain ) );
    // }
    return $return;
  }
}

if ( !function_exists( 'prp_get_section_name' ) ) {
  /**
   * Gets the section name from the current line in the readme file.
   *
   * @author dartiss
   * @since 1.0
   *
   * @param string $readme_line The current line in the readme file.
   * @param int $start_pos The position in the line to start
   * looking from.
   * @return string The section name given in the current line in
   * the readme file.
   */
  function prp_get_section_name( string $readme_line, int $start_pos ): string {

    // prp_log( 'function', __FUNCTION__ );
    // prp_log( 'arguments', func_get_args() );

    $hash_pos = strpos( $readme_line, '#', $start_pos + 1 );

    if ( $hash_pos ) {
      $section = substr( $readme_line, $start_pos + 1, $hash_pos - $start_pos - 2 );
    } else {
      $section = substr( $readme_line, $start_pos + 1 );
    }

    // prp_log( __( '  Get section name:', plugin_readme_parser_domain ) );
    // prp_log( __( '  section name: \'' . $section . '\'', plugin_readme_parser_domain ) );

    return $section;
  }
}

if ( !function_exists( 'prp_display_links' ) ) {
  /**
   * Displays predefined links relevant to the plugin.
   *
   * The links displayed are:
   * * the ZIP archive of the latest version of the plugin for
   * download
   * * the official WordPress page for the plugin
   * * the WordPress support forum for the plugin
   *
   * Display of the links is controlled by the 'exclude'/'include'
   * parameters of the 'readme' shortcode.
   *
   * @author dartiss, pandammonium
   * @since 1.2
   * @since 2.0.0 Uses constants for the URLs. Updates the link
   * text, adding $plugin_nice_name to avoid using the vague 'this
   * plugin' in display text.
   *
   * @param string $download The link from where the most recent
   * version of the plugin can be downloaded.
   * @param string $target The target to be used in the link
   * (e.g._blank).
   * @param string $nofollow Whether the link should include
   * 'nofollow' or not.
   * @param string $version The plugin version number.
   * @param string[] $mirror Links to any mirrors of the plugin that
   * can be used as alternatives to download the plugin from.
   * @param string $plugin_name The name of the plugin (in kebab
   * case) for use in URLs.
   * @param string $plugin_nice_name The title of the plugin, for
   * use in display text.
   * @return string The HTML for the links section.
   *
   * @todo Consider replacing the long list of arguments with an
   * array. Alternatively, move to Generate_Output.
   * @todo Consider making the link text customisable. How?
   * @todo Consider throwing an exception if the version cannot be
   * found.
   */
  function prp_display_links( string $download, string $target, string $nofollow, string $version, array $mirror, string $plugin_name, string $plugin_nice_name ): string {

    // prp_log( 'function', __FUNCTION__ );
    // prp_log( 'arguments', func_get_args() );

    // prp_log( __( '  Display links:', plugin_readme_parser_domain ) );
    // prp_log( __( '  download link: \'' . $download . '\'', plugin_readme_parser_domain ) );
    // prp_log( __( '  target:        \'' . $target . '\'', plugin_readme_parser_domain ) );
    // prp_log( __( '  nofollow:      \'' . $nofollow . '\'', plugin_readme_parser_domain ) );
    // prp_log( __( '  version:       \'' . $version . '\'', plugin_readme_parser_domain ) );

    $crlf = "\r\n";

    $output = '<div markdown="1" class="np-links">' . $crlf . '## Links ##' . $crlf . $crlf;

    if ( $version !== '' ) {
      $output .= '<a class="np-download-link" href="' . $download . '" target="' . $target . '"' . $nofollow . '>Download the latest version of ' . $plugin_nice_name . '</a> (v' . $version . ')<br /><br />' . $crlf;

      // prp_log( __( '  version found; outputting download link', plugin_readme_parser_domain ) );

      // If any mirrors exist, add them to the output:
      if ( $mirror[ 0 ] > 0 ) {
        for ( $m = 1; $m <= $mirror[ 0 ]; $m++ ) {
          $output .= '<a class="np-download-link" href="' . $mirror[ $m ] . '" target="' . $target . '"' . $nofollow . '>Download ' . $plugin_nice_name . ' from mirror ' . $m . '</a><br />' . $crlf;
          // prp_log( __( '  mirror[' . $m . ']: ', plugin_readme_parser_domain ) . $mirror[ $m ] );
        }
        $output .= '<br />';
      } else {
        // prp_log( __( '  mirror:        \'none\'', plugin_readme_parser_domain ) );
      }

    } else {
      // prp_log( __( '  no version, therefore no download link', plugin_readme_parser_domain ) );
      $output .= '<span class="np-download-link"><span class="error">' . plugin_readme_parser_name . '</span>: No download link is available as the version number could not be found</span><br /><br />' . $crlf;
    }

    $output .= '<a href="' . WP_PLUGIN_PAGE_URL . $plugin_name . '/" target="' . $target . '"' . $nofollow . '>Visit the official WordPress plugin page for ' . $plugin_nice_name . '</a><br />' . $crlf;
    $output .= '<a href="' . WP_PLUGIN_SUPPORT_URL . $plugin_name . '" target="' . $target . '"' . $nofollow . '>Need help? Visit the WordPress support forum for ' . $plugin_nice_name . '</a><br />' . $crlf . '</div>' . $crlf;

    return $output;
  }
}

if ( !function_exists( 'prp_check_img_exists' ) ) {
  /**
   * Checked the image existed on the WordPress SVN server.
   *
   * @author dartiss, pandammonium
   * @since 1.2
   * @deprecated 2.0.0 This function is obsolete because resulted
   * in an HTTP 403 (Forbidden) error from the WordPress SVN
   * server. It currently returns an empty strings, and will be
   * removed from future versions of this plugin.
   *
   * @param string $filename The filename minus its extension.
   * @param string $ext The file extension.
   * @return string An empty string.
   */
  function prp_check_img_exists( string $filename, string $ext ): string {
    return '';
  }
}

if ( !function_exists( 'prp_format_list' ) ) {
  /**
   * Formats lists for display.
   *
   * Each item in the list is cleaned up, and formatted as an HTML
   * link, ready for display.
   *
   * @author dartiss, pandammonium
   * @since 1.0
   * @since 2.0.0 Renamed from 'prp_strip_list' to
   * 'prp_format_list'. Uses constants for the URLs. Renames return
   * variable for semantic clarity. Adds error handling.
   *
   * @param string $list A list of items, currently one of:
   * * plugin contributors
   * * plugin tags
   * @param string $type The type of list, currently one of:
   * * 't': tags
   * * 'c': contributors
   * @param string $target Link target.
   * @param string $nofollow Link nofollow.
   * @throws PRP_Exception if an unsupported list type is given.
   * @return string The list formatted as HTML.
   */
  function prp_format_list( string $list, string $type, string $target, string $nofollow ): string {

    // prp_log( 'function', __FUNCTION__ );
    // prp_log( 'arguments', func_get_args() );

    // prp_log( 'list', $list );
    // prp_log( 'type', $type );
    // prp_log( 'target', $target );
    // prp_log( 'nofollow', $nofollow );

    $url = '';
    switch ( $type ) {
      case 'c':
        $url = WP_USER_DIR_URL;
      break;
      case 't':
        $url = WP_PLUGIN_TAGS_URL;
      break;
      default:
        throw new PRP_Exception( 'Invalid list type found: ' . $type, PRP_Exception::PRP_ERROR_BAD_DATA );
    }

    $startpos = 0;
    $number = 0;
    $endpos = strpos( $list, ',', 0 );
    $html = '';

    while ( $endpos !== false ) {
      ++$number;
      $item = trim( substr( $list, $startpos, $endpos - $startpos ) );
      // prp_log( 'item', $item );
      if ( $number > 1 ) {
        $html .= ', ';
      }
      $html .= '<a href="' . $url . $item . '" target="' . $target . '"' . $nofollow . '>' . $item . '</a>';
      $startpos = $endpos + 1;
      $endpos = strpos( $list, ',', $startpos );
    }

    $item = trim( substr( $list, $startpos ) );
    if ( $number > 0 ) {
      $html .= ', ';
    }
    $html .= '<a href="' . $url . $item . '" target="' . $target . '"' . $nofollow . '>' . $item . '</a>';

    return $html;
  }
}

if ( !function_exists( 'prp_get_file' ) ) {
  /**
   * Gets the given file from WordPress.
   *
   * Uses WordPress API to fetch a file from the WOrdPress server
   * and to and check the response code (rc):
   * * success: 0
   * * failure: -1
   *
   * @author dartiss, pandammonium
   * @since 1.6
   * @since 2.0.0 Enhances error handling.
   *
   * @param string $file_url The URL of the file to fetch.
   * @param bool $header True if only the headers should be
   * fetched; false to fetch everything.
   * @return string[] The file contents and the server response.
   *
   * @todo Make error handling fully reliant on WP_Error and
   * exceptions rather than error codes with magic numbers that
   * have to be known outside this file.
   */
  function prp_get_file( string $file_url, bool $header = false ): array {

    // prp_log( 'function', __FUNCTION__ );
    // prp_log( 'arguments', func_get_args() );

    // prp_log( 'file url', $file_url );
    // prp_log( 'header', $header );

    $pos = strpos( strtolower( $file_url ), WP_PLUGIN_DIR_URL . '/' );
    if ( 0 === $pos ) {
      throw new PRP_Exception( 'The URL is missing the plugin name: <samp>' . substr( $file_url, $pos, strlen( $repo ) ) . '&lt;plugin-name&gt;/</samp>', PRP_Exception::PRP_ERROR_BAD_URL );
    }

    $file_return = array();
    $rc = 0;
    $error = '';
    if ( $header ) {
      $result = wp_remote_head( $file_url );
      if ( is_wp_error( $result ) ) {
        $error = 'Header: ' . $result -> get_error_message();
        $rc = -1;
        // throw new PRP_Exception( $error . '(' . $result->get_error_code . ')' );
      }
    } else {
      $result = wp_remote_get( $file_url );
      if ( is_wp_error( $result ) ) {
        $error = 'Body: ' . $result -> get_error_message();
        $rc = -1;
        // throw new PRP_Exception( $error . '(' . $result->get_error_code . ')' );
      } else {
        if ( isset( $result[ 'body' ] ) ) {
          $file_return[ 'file' ] = $result[ 'body' ];
        }
      }
    }

    $file_return[ 'error' ] = $error;
    $file_return[ 'rc' ] = $rc;
    if ( is_wp_error( $result ) ) {
      // prp_log( '  WP Error', $result );
      throw new PRP_Exception( $result->get_error_message(), $result->get_error_code() );
    } else {
      // prp_log( 'type of response', gettype( $result[ 'response' ] ) );
      // prp_log( 'response', $result[ 'response' ] );
      // prp_log( 'type of http response', gettype( $result[ 'http_response' ] ) );
      // prp_log( 'http response – null?', null === $result[ 'http_response' ] );
      if ( isset( $result[ 'response' ][ 'code' ] ) ) {
        $file_return[ 'response' ] = $result[ 'response' ][ 'code' ];
        // prp_log( 'file return', $file_return );
        // prp_log( 'response', $file_return[ 'response' ] );
        // prp_log( 'rc', $file_return[ 'rc' ] );
        // if ( isset( $file_return[ 'file' ] ) ) {
        //   prp_log( 'file', $file_return[ 'file' ] );
        // }
      }
      if ( isset( $result[ 'http_response' ] ) ) {
        $response = $result[ 'http_response' ]->get_response_object();
        // prp_log( 'type of response object', gettype( $response ) );
        try {
          $response->throw_for_status( false );
        } catch ( Exception $e) {
          throw new PRP_Exception( 'The URL <samp>' . $file_url . '</samp> of the readme file returned a <samp>' . $e->getMessage() . '</samp> error', PRP_Exception::PRP_ERROR_BAD_URL );
        }
      }

    }

    // prp_log( __( '  file out:    contents of readme file', plugin_readme_parser_domain ) );
    // prp_log( '  file out', $result );
    // prp_log( __( '  file return: contents of readme file', plugin_readme_parser_domain ) );
    // prp_log( '  file return', $file_return );

    // prp_log( 'file return (error)', $file_return[ 'error' ] );
    // prp_log( 'file return (rc)', $file_return[ 'rc' ] );
    // prp_log( 'file return (response)', $file_return[ 'response' ] );
    return $file_return;
  }
}

if ( !function_exists( 'prp_get_list' ) ) {
  /**
   * Extract parameters to an array
   *
   * Function to extract parameters from an input string and
   * add to an array
   *
   * @since 1.0
   *
   * @param string $input The input string that needs to be split.
   * @param string $separator The separator character used to split
   * the input string. If not specified, it defaults to a comma (,).
   * @param string $type Indicates the type of list; only used for debug purposes.
   * @return     string[]  Array of parameters.
   */
  function prp_get_list( string $input, string $separator = '', string $type = '' ): array {   // Version 1.2

    // prp_log( 'function', __FUNCTION__ );
    // prp_log( 'arguments', func_get_args() );


    // prp_log( __( '  Get \'' . $type . '\' list:', plugin_readme_parser_domain ) );
    // prp_log( __( '  input:     \'' . $input . '\'', plugin_readme_parser_domain ) );
    // prp_log( __( '  separator: \'' . $separator . '\'', plugin_readme_parser_domain ) );

    if ( $separator === '' ) {
      $separator = ',';
    }
    $comma = strpos( strtolower( $input ), $separator );

    $item = 0;
    while ( $comma !== false ) {
      $item++;
      $content[ $item ] = substr( $input, 0, $comma );
      $input = substr( $input, $comma + strlen( $separator ) );
      $comma = strpos( $input, $separator );
    }

    if ( $input !== '' ) {
      $item++;
      $content[ $item ] = substr( $input, 0 );
    }

    $content[ 0 ] = $item;
    // prp_log( $content[0], '  content[0]:' );
    return $content;
  }
}

if ( !function_exists( 'prp_toggle_global_shortcodes' ) ) {
  /**
   * Toggle the shortcodes so that any shortcodes in the readme file
   * aren't expanded. Expanded shortcodes in the readme files cause
   * problems if they're used to provide examples of use of the
   * plugin's shortcode.
   *
   * Some plugins change this filter’s priority, so clear the
   * global list of registered shortcodes temporarily, except for
   * this plugin's readme_info, which is needed.
   *
   * @author pandammonium
   * @since 2.0.0
   * @link https://wordpress.stackexchange.com/a/115176 Stack
   * Exchange was used to inform the implementation of this
   * function.
   *
   * @param string $content The readme file content.
   * @param string $exceptions The shortcodes to keep active.
   * @return string|WP_Error The readme file content.
   */
  function prp_toggle_global_shortcodes( string $content ): string|WP_Error {

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
    }
    return $content;
  }

  add_filter( 'the_content', 'prp_toggle_global_shortcodes', -1 );
  add_filter( 'the_content', 'prp_toggle_global_shortcodes', PHP_INT_MAX );
}

if ( !function_exists( 'prp_line_is_head_meta_data' ) ) {
  /**
   * Checks that the the readme file line is head meta data.
   *
   * Tests to see whether the current line in the readme file is a
   * line in the head meta data (e.g. tags, licence, contributors)
   * or not.
   *
   * @author pandammonium
   * @since 2.0.0 Abstracted from Generate_Output.
   *
   * @param string $line_in_file The current line of the readme
   * file being parsed.
   * @return bool True if the current line in the readme file is
   * part of the head meta data, otherwise false.
   */
  function prp_line_is_head_meta_data( string $line_in_file ): bool {

    // prp_log( 'function', __FUNCTION__ );
    // prp_log( 'arguments', func_get_args() );

    if ( ( 'Contributors:' === substr( $line_in_file, 0, 13 ) ) or
         ( 'Donate link:' === substr( $line_in_file, 0, 12 ) ) or
         ( 'Tags:' === substr( $line_in_file, 0, 5 ) ) or
         ( 'Requires at least:' === substr( $line_in_file, 0, 18 ) ) or
         ( 'Requires PHP:' === substr( $line_in_file, 0, 13 ) ) or
         ( 'Tested up to:' === substr( $line_in_file, 0, 13 ) ) or
         ( 'Stable tag:' === substr( $line_in_file, 0, 11 ) ) or
         ( 'License URI:' === substr( $line_in_file, 0, 12 ) ) or
         ( 'License:' === substr( $line_in_file, 0, 8 ) ) ) {
      return true;
    } else {
      return false;
    }
  }
}

if ( !function_exists( 'prp_add_head_meta_data_to_output' ) ) {
  /**
   * Determine which parts of the head meta data, if any, should be
   * added to the output.
   *
   * The head comprises the plugin title/name, the meta data and a
   * summary/description of the plugin. There may be one or more
   * blank lines. This function deals with the meta data only.
   *
   * The meta data is the labelled data, such as tags, licence and
   * contributors. It is added to the output if one of the
   * following is true:
   * * $show_head === $show_meta === true
   * * $show_head === false and $show_meta === true.
   *
   * The summary is added to the output if one of the following is
   * true:
   * * $show_head === $show_meta === true
   * * $show_head === true and $show_meta === false.
   *
   * @author pandammonium
   * @since 2.0.0 Abstracted from Generate_Output.
   *
   * @param bool $show_head If true, the head should be output.
   * If false, the head should not be output.
   * @param bool $show_meta If true, the meta data should be
   * output. If false, the meta data should not be output.
   * @param string &$line_in_file The line in the readme file
   * currently being parsed. It is passed by reference so that any
   * amendments may be made as necessary.
   * @param string[] $metadata The metadata from the head of the
   * file, e.g. tags, version, licence
   * @return bool True if this line should be added to the output,
   * otherwise false.
   */
  function prp_add_head_meta_data_to_output( bool $show_head, bool $show_meta, string &$line_in_file, array $metadata ): bool {

    // prp_log( 'function', __FUNCTION__ );
    // prp_log( 'arguments', func_get_args() );

    // prp_log( 'Args of prp_add_head_meta_data_to_output()', array(
    //   'show head' => $show_head,
    //   'show meta' => $show_meta,
    //   'line in file' => $line_in_file,
    //   'meta data' => $metadata
    // ) );

    $add_to_output = true;

    if ( $show_head || $show_meta ) {

      // prp_log_truncated_line( 'checking ' . ( '' === $line_in_file ? '\'\'' : $line_in_file ) );

      if ( prp_line_is_head_meta_data( $line_in_file ) ) {

        // Process meta data from top

        if ( !$show_meta ) {
          // prp_log( __( 'exclude all meta', plugin_readme_parser_domain ) );
          $add_to_output = false;

        } else if ( ( 'Requires at least:' === substr( $line_in_file, 0, 18 ) ) &&
             ( prp_is_it_excluded( 'requires', $metadata[ 'exclude' ] ) ) ) {
          // prp_log( __( 'exclude WP req', plugin_readme_parser_domain ) );
          $add_to_output = false;

        } else if ( ( 'Requires PHP:' === substr( $line_in_file, 0, 18 ) ) &&
             ( prp_is_it_excluded( 'requires php', $metadata[ 'exclude' ] ) ) ) {
          // prp_log( __( 'exclude PHP req', plugin_readme_parser_domain ) );
          $add_to_output = false;

        } else if ( ( 'Tested up to:' === substr( $line_in_file, 0, 13 ) ) &&
             ( prp_is_it_excluded( 'tested', $metadata[ 'exclude' ] ) ) ) {
          // prp_log( __( 'exclude test', plugin_readme_parser_domain ) );
          $add_to_output = false;

        } else if ( ( 'License:' === substr( $line_in_file, 0, 8 ) ) &&
             ( prp_is_it_excluded( 'license', $metadata[ 'exclude' ] ) ) ) {
          // prp_log( __( 'exclude licence', plugin_readme_parser_domain ) );
          $add_to_output = false;

        } else if ( 'Contributors:' === substr( $line_in_file, 0, 13 ) ) {
          if ( prp_is_it_excluded( 'contributors', $metadata[ 'exclude' ] ) ) {
          // prp_log( __( 'exclude contrib', plugin_readme_parser_domain ) );
            $add_to_output = false;
          } else {
            // Show contributors and tags using links to WordPress pages
            $line_in_file = substr( $line_in_file, 0, 14 ) . prp_format_list( substr( $line_in_file, 14 ), 'c', $metadata[ 'target' ], $metadata[ 'nofollow' ] );
          }

        } else if ( 'Tags:' === substr( $line_in_file, 0, 5 ) ) {
          if ( prp_is_it_excluded( 'tags', $metadata[ 'exclude' ] ) ) {
          // prp_log( __( 'exclude tags', plugin_readme_parser_domain ) );
            $add_to_output = false;
          } else {
            $line_in_file = substr( $line_in_file, 0, 6 ) . prp_format_list( substr( $line_in_file, 6 ), 't', $metadata[ 'target' ], $metadata[ 'nofollow' ] );
          }

        } else if ( 'Donate link:' === substr( $line_in_file, 0, 12 ) ) {
          if ( prp_is_it_excluded( 'donate', $metadata[ 'exclude' ] ) ) {
          // prp_log( __( 'exclude donate', plugin_readme_parser_domain ) );
            $add_to_output = false;
          } else {
            // Convert the donation link to a hyperlink
            $text = substr( $line_in_file, 13 );
            $line_in_file = substr( $line_in_file, 0, 13 ) . '<a href="' . $text . '">' . $text . '</a>';
          }

        } else if ( 'License URI:' === substr( $line_in_file, 0, 12 ) ) {
          if ( prp_is_it_excluded( 'license uri', $metadata[ 'exclude' ] ) ) {
          // prp_log( __( 'exclude lic uri', plugin_readme_parser_domain ) );
            $add_to_output = false;
          } else {
            // Convert the licence URL to a hyperlink
            $text = substr( $line_in_file, 13 );
            $line_in_file = substr( $line_in_file, 0, 13 ) . '<a href="' . $text . '">' . $text . '</a>';
          }

        } else if ( 'Stable tag:' === substr( $line_in_file, 0, 11 ) ) {
          if ( prp_is_it_excluded( 'stable', $metadata[ 'exclude' ] ) ) {
          // prp_log( __( 'exclude stab tag', plugin_readme_parser_domain ) );
            $add_to_output = false;
          } else {
            // Link to the download given by the version
            $line_in_file = substr( $line_in_file, 0, 12 ) . '<a href="' . $metadata[ 'download' ].'" style="max-width: 100%;">' . $metadata[ 'version' ] . '</a>';
          }
        }

        // If one of the header tags, add a BR tag to the end of the line.
        // As the output is meant to be XHTML, the BR tag needs to be closed. The proper way to do this is to have no space before the slash.

        $line_in_file .= '<br/>';
      } else {
        // prp_log( __( 'line is not meta data but is in head; add to output', plugin_readme_parser_domain ), ( $add_to_output ? 'true' : 'false' ) );
        return $show_head;
      }
    } else {
      $add_to_out = false;
    }

    // prp_log( __( 'add head meta data to output', plugin_readme_parser_domain ), ( $add_to_output ? 'true' : 'false' ) );
    return $add_to_output;
  }
}
?>
