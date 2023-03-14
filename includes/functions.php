<?php
/**
 * Functions
 *
 * Functions called by main output generator
 *
 * @package  Pandammonium-Readme-Parser
 * @since  1.2
 */

// If this file is called directly, abort:
defined( 'ABSPATH' ) or die();
defined( 'WPINC' ) or die();

if ( !function_exists( 'prp_log' ) ) {
/**
 * Prints a message to the debug log file.
 *
 * @since 2.0.0
 *
 * @param string  message_name  A name to associate with the
 * message. This is useful if logging multiple messages.
 * @param string / array $message    The message to be logged.
 * @param bool  $error  (optional)  Whether the message is about an error or not. Default is false, the message is not about an error.
 * @param bool  $echo  (optional) Forces the message name and message to be displayed on the web page; overrides WP_DEBUG_DISPLAY. Default is false, the message name and message will not be displayed on the web page.
 *
 */
  function prp_log( string $message_name, mixed $message = null, bool $error = false, bool $echo = false ): string {

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
      case 'string':
      case 'integer':
        $output = print_r( $header . $divider . trim( $message ), true );
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
      $error_label = 'error';
      if ( false === stripos( $output, 'error' ) ) {
        $output = 'error' . $output;
      } else {
        // Make sure the error label is upper case
        $count = 0;
        $output = str_ireplace( $error_label, strtoupper($error_label), $output, $count);
        // error_log( 'Changed case of error label ' . $count . ' time' . (1 === $count ? '' : 's') );
      }
    }

    $prefix = ( strncmp( $output, plugin_readme_parser_name, strlen( plugin_readme_parser_name ) ) === 0 ) ? '' : 'PRP | ';
    // $prefix = plugin_readme_parser_name . ' | ';

    if ( ( $debugging && $debug_logfile ) ||
         ( $error && !$echo ) ) {
      error_log( $prefix . wp_strip_all_tags( trim( $output ) ) );
    }

    if ( ( $debugging && $debug_display ) ||
         ( $error && $echo ) ||
         ( $echo ) ) {

      $delim = ':';
      $pos = strpos( $output, $delim );
      if ( $pos !== false ) {
        $output = '<b>' . str_replace( $delim, $delim . '</b>', $output );
      }
      switch ( $message_type ) {
        case 'string':
        case 'integer':
          $output = '<p>' . $output . '</p>';
        break;
        default:
          $output = '<pre>' . $output . '</pre>';
        break;
      }
    }
    return $output;
  }
}

if ( !function_exists( 'prp_get_wp_error_string' ) ) {
  function prp_get_wp_error_string( WP_Error $error, bool $echo = false ):string {

    $output = plugin_readme_parser_name .
        ' error ' .
        trim( print_r( $error->get_error_code(), true ) ).
        ': ' .
        trim( print_r( $error->get_error_message(), true ) ) .
        ( empty( $error->get_error_data() ) ? '' : '. \'' . trim( print_r( $error->get_error_data(), true ) ) . '\'' );

    return $echo ? '<p>' . $output . '.</p>' : $output;
  }
}

if ( !function_exists( 'prp_log_truncated_line' ) ) {
  function prp_log_truncated_line( string $line, int $line_number = -1 ): void {

    // prp_log( 'function', __FUNCTION__ );

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
   * Is It Excluded?
   *
   * Function to check if the current section is excluded or not.
   * The screenshots section is always excluded because this type of access to
   *  the WordPress SVN server is forbidden.
   *
   * @since  1.0
   *
   * @param  $tofind   string  Section name
   * @param  $exclude    string  List of excluded sections
   * @return       bool  true or false, depending on whether the section was valid
   */
  function prp_is_it_excluded( string $tofind, string $exclude ): bool {

    // prp_log( 'function', __FUNCTION__ );

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
   * Get Section Name
   *
   * Function to get name of readme section
   *
   * @since  1.0
   *
   * @param  $readme_line  string  Line from readme
   * @param  $start_pos    int  Position of line to look from
   * @return       string  Section name
   */
  function prp_get_section_name( string $readme_line, int $start_pos ): string {

    // prp_log( 'function', __FUNCTION__ );

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
 * Display links section
 *
 * Return the section that displays download links and links to assorted
 * WordPress sections
 *
 * @since  1.2
 *
 * @param  $download   string  Download link
 * @param  $target     string  Link target
 * @param  $nofollow   string  Link nofollow
 * @param  $version    string  Version number
 * @param  $mirror     string[]  Array of mirror links
 * @param  $plugin_name  string  Plugin name
 * @return       string  Output
 */
  function prp_display_links( string $download, string $target, string $nofollow, string $version, array $mirror, string $plugin_name ): string {

    // prp_log( 'function', __FUNCTION__ );

    // prp_log( __( '  Display links:', plugin_readme_parser_domain ) );
    // prp_log( __( '  download link: \'' . $download . '\'', plugin_readme_parser_domain ) );
    // prp_log( __( '  target:        \'' . $target . '\'', plugin_readme_parser_domain ) );
    // prp_log( __( '  nofollow:      \'' . $nofollow . '\'', plugin_readme_parser_domain ) );
    // prp_log( __( '  version:       \'' . $version . '\'', plugin_readme_parser_domain ) );

    $crlf = "\r\n";

    $output = '<div markdown="1" class="np-links">' . $crlf . '## Links ##' . $crlf . $crlf;

    if ( $version !== '' ) {
      $output .= '<a class="np-download-link" href="' . $download . '" target="' . $target . '"' . $nofollow . '>Download the latest version</a> (v' . $version . ')<br /><br />' . $crlf;

      // prp_log( __( '  version found; outputting download link', plugin_readme_parser_domain ) );

      // If mirrors exist, add them to the output

      if ( $mirror[ 0 ] > 0 ) {
        for ( $m = 1; $m <= $mirror[ 0 ]; $m++ ) {
          $output .= '<a class="np-download-link" href="' . $mirror[ $m ] . '" target="' . $target . '"' . $nofollow . '>Download from mirror ' . $m . '</a><br />' . $crlf;
          // prp_log( __( '  mirror[' . $m . ']: ', plugin_readme_parser_domain ) . $mirror[ $m ] );
        }
        $output .= '<br />';
      } else {
        // prp_log( __( '  mirror:        \'none\'', plugin_readme_parser_domain ) );
      }

    } else {

      // prp_log( __( '  no version, therefore no download link', plugin_readme_parser_domain ) );

      $output .= '<span class="np-download-link error">No download link is available as the version number could not be found</span><br /><br />' . $crlf;

      // $output .= prp_report_error( __( '', '' ), '', false );

      // $output .= prp_report_error( __( '<span class="np-download-link>No download link is available as the version number could not be found</span>', plugin_readme_parser_domain ), plugin_readme_parser_name, false );

    }

    $output .= '<a href="https://wordpress.org/extend/plugins/' . $plugin_name . '/" target="' . $target . '"' . $nofollow . '>Visit the official WordPress plugin page</a><br />' . $crlf;
    $output .= '<a href="https://wordpress.org/support/plugin/' . $plugin_name . '" target="' . $target . '"' . $nofollow . '>View for WordPress forum for this plugin</a><br />' . $crlf . '</div>' . $crlf;

    return $output;
  }
}

if ( !function_exists( 'prp_check_img_exists' ) ) {
  /**
   * Check image exists
   *
   * Function to check if an image files with a specific extension exists
   * This fumction results in an HTTP 403 (Forbidden) error from the
   * server, therefore all it does (for) now is return false
   *
   * @since  1.2
   *
   * @param  $filename   string  Filename
   * @param  $ext    string  File extension
   * @return       string  Valid extension or blank
   */
  function prp_check_img_exists( string $filename, string $ext ): string {

    // prp_log( 'function', __FUNCTION__ );

    // $file_url = $filename . $ext;
    // $file_exists = false;

    // prp_log( __( '  Check image exists:', plugin_readme_parser_domain ) );

    // prp_log( __( '  file path', plugin_readme_parser_domain ), $file_url );

    // prp_log( __( '  file exists', plugin_readme_parser_domain ), file_exists( $file_url ) );

    // prp_log( __( '  mime type', plugin_readme_parser_domain ), mime_content_type( $file_url ) );

    // $file_contents = (bool)@file_get_contents($file_url, false, stream_context_create([
    //     'http' => [
    //         'method' => 'HEAD',
    //         'ignore_errors' => true,
    //     ],
    // ]));

    // prp_log( __( '  can get file contents', plugin_readme_parser_domain ), $file_contents );

    // $file_exists = @mime_content_type( $file_url ) === 'image/' . $ext;
    // $file_exists = @file_exists( $file_url );

    // $file_exists = (bool)@file_get_contents($file_url, false, stream_context_create([
    //     'http' => [
    //         'method' => 'HEAD',
    //         'ignore_errors' => true,
    //     ],
    // ]));


    return false;

    // if ( $file_exists ) {
    //   prp_log( __( '\'' . $filename . $ext . '\' exists: true', plugin_readme_parser_domain ) );
    //   return $ext;
    // } else {
    //   prp_log( __( '\'' . $filename . $ext . '\' exists: false', plugin_readme_parser_domain ) );
    //   return false;
    // }
  }
}

if ( !function_exists( 'prp_strip_list' ) ) {
  /**
   * Strip List
   *
   * Function to strip user or tag lists and add links
   *
   * @since  1.0
   *
   * @param  $list     string  List of e.g. tags, categories
   * @param  $type   string  Type of list, e.g. tags ('t'), categories ('c')
   * @param  $target   string  Link target
   * @param  $nofollow   string  Link nofollow
   * @return       string  HTML output
   */
  function prp_strip_list( string $list, string $type, string $target, string $nofollow ): string {

    // prp_log( 'function', __FUNCTION__ );

    // prp_log( __( '  Strip list:', plugin_readme_parser_domain ) );
    // prp_log( __( '  list:     \'' . $list. '\'', plugin_readme_parser_domain ) );
    // prp_log( __( '  type:     \'' . $type . '\'', plugin_readme_parser_domain ) );
    // prp_log( __( '  target:   \'' . $target . '\'', plugin_readme_parser_domain ) );
    // prp_log( __( '  nofollow: \'' . $nofollow . '\'', plugin_readme_parser_domain ) );

    if ( $type === 'c' ) {
      $url = 'https://profiles.wordpress.org/users/';
    } else if ( $type === 't' ) {
      $url = 'https://wordpress.org/extend/plugins/tags/';
    } else {
      $url = '';
      throw new PRP_Exception( 'Invalid list type found: ' . $type, PRP_Exception::PRP_ERROR_BAD_DATA );
    }

    $startpos = 0;
    $number = 0;
    $endpos = strpos( $list, ',', 0 );
    $return = '';

    while ( $endpos !== false ) {
      ++$number;
      $name = trim( substr( $list, $startpos, $endpos - $startpos ) );
      // prp_log( __( '  name:     \'' . $name . '\'', plugin_readme_parser_domain ) );
      if ( $number > 1 ) {
        $return .= ', ';
      }
      $return .= '<a href="' . $url . $name . '" target="' . $target . '"' . $nofollow . '>' . $name . '</a>';
      $startpos = $endpos + 1;
      $endpos = strpos( $list, ',', $startpos );
    }

    $name = trim( substr( $list, $startpos ) );
    if ( $number > 0 ) {
      $return .= ', ';
    }
    $return .= '<a href="' . $url . $name . '" target="' . $target . '"' . $nofollow . '>' . $name . '</a>';

    return $return;
  }
}

if ( !function_exists( 'prp_get_file' ) ) {
  /**
   * Fetch a file (1.6)
   *
   * Use WordPress API to fetch a file and check results
   * RC is 0 to indicate success, -1 a failure
   *
   * @since  [version number]
   *
   * @param  string  $file_url   The url of the file to fetch
   * @param  bool  $header   True to only get headers; otherwise false
   * @return string[]    Array containing file contents and response
   */
  function prp_get_file( string $file_url, bool $header = false ): array {

    // prp_log( 'function', __FUNCTION__ );

    // prp_log( __( '  Get file:', plugin_readme_parser_domain ) );
    // prp_log( __( '  file in:     \'' . $file_url. '\'', plugin_readme_parser_domain ) );
    // prp_log( __( '  header:      ' . ( $header ? 'true' : 'false' ), plugin_readme_parser_domain ) );

    $file_return = array();
    $rc = 0;
    $error = '';
    if ( $header ) {
      $result = wp_remote_head( $file_url );
      if ( is_wp_error( $result ) ) {
        $error = 'Header: ' . $result -> get_error_message();
        $rc = -1;
      }
    } else {
      $result = wp_remote_get( $file_url );
      if ( is_wp_error( $result ) ) {
        $error = 'Body: ' . $result -> get_error_message();
        $rc = -1;
      } else {
        if ( isset( $result[ 'body' ] ) ) {
          $file_return[ 'file' ] = $result[ 'body' ];
          // prp_log( 'file', $file_return[ 'file' ] );
        }
      }
    }
    // prp_log( '  error', $error );
    // prp_log( '  rc', $rc );

    $file_return[ 'error' ] = $error;
    $file_return[ 'rc' ] = $rc;
    if ( is_wp_error( $result ) ) {
      // prp_log( '  WP Error', $result );
      throw new PRP_Exception( $result->get_error_message(),intval( $result->get_error_code() ) );
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
      $response = $result[ 'http_response' ]->get_response_object();
      // prp_log( 'type of response object', gettype( $response ) );
      try {
        $response->throw_for_status( false );
      } catch ( Exception $e) {
        throw new PRP_Exception( 'The URL <samp>' . $file_url . '</samp> of the readme file returned a <samp>' . $e->getMessage() . '</samp> error', PRP_Exception::PRP_ERROR_BAD_URL );
      // } finally {
      }

    }

    // prp_log( __( '  file out:    contents of readme file', plugin_readme_parser_domain ) );
    // prp_log( '  file out', $result );
    // prp_log( __( '  file return: contents of readme file', plugin_readme_parser_domain ) );
    // prp_log( '  file return', $file_return );

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
   * @since  1.0
   *
   * @param  $input    string  The input string that needs to be split.
   * @param  $separator  string  The separator character used to split
   * the input string. If not specified, it defaults to a comma (,).
   * @param  $type       string  Indicates the type of list; only used for debug purposes.
   * @return     string[]  Array of parameters.
   */
  function prp_get_list( string $input, string $separator = '', string $type = '' ): array {   // Version 1.2

    // prp_log( 'function', __FUNCTION__ );

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
 * aren't expanded. Expanded shortcodes in the readme files cause problems if
 * they're used to provide examples of use of the plugin's shortcode.
 *
 * Some plugins change this filter’s priority, so clear the global list of
 * registered shortcodes temporarily, except for this plugin's readme_info,
 * which is needed.
 *
 * @since  2.0.0
 * @link   https://wordpress.stackexchange.com/a/115176
 *
 * @param  $content     string  The readme file content
 * @param  $exceptions  string  The shortcodes to keep active
 * @return string[]  The readme file content
 */
  function prp_toggle_global_shortcodes( string $content ): string {

    // prp_log( 'function', __FUNCTION__ );

    $file = plugin_dir_path( __DIR__ );
    // prp_log( __( 'Plugin directory: ', plugin_readme_parser_domain ) . $file );
    if ( str_contains( $file, plugin_readme_parser_filename ) ) {

      static $original_shortcodes = array();

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
      // Can't throw an exception here because it won't be caught by the plugin, presumably because it's used as a filter on `the_content`. Use WP_Error instead.

      $error = new WP_Error();
      $error->add( PRP_Exception::PRP_ERROR_BAD_INPUT, 'Wrong plugin. Expected <samp><kbd>' . plugin_readme_parser_domain . '</kbd></samp>; got <samp><kbd>' . $file . '</kbd></samp>' );
      // prp_log( 'has errors', $error->has_errors() );
      // prp_log( 'error', $error );
      // prp_log( 'error code', $error->get_error_code() );
      // prp_log( 'error message', $error->get_error_message() );
      return prp_log( 'error', $error, true, true );
    }
    return $content;
  }

  add_filter( 'the_content', 'prp_toggle_global_shortcodes', -1 );
  add_filter( 'the_content', 'prp_toggle_global_shortcodes', PHP_INT_MAX );
}

if ( !function_exists( 'prp_line_is_head_meta_data' ) ) {
  /**
   * Tests to see whether the current line in the readme file is a line in the head meta data (e.g. tags, licence, contributors) or not.
   *
   * @param $line_in_file  string  The current line of the readme file being
   * parsed.
   * @return bool  Returns true of the current line in the readme file is
   * part of the head meta data, otherwise false.
   */
  function prp_line_is_head_meta_data( string $line_in_file ): bool {

    // prp_log( 'function', __FUNCTION__ );

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
   * Determine which parts of the head meta data, if any, should be added to the output.
   *
   * The head comprises the plugin title/name, the meta data and a summary/
   * description of the plugin. There may be one or more blank lines. This function deals with the meta data only.
   *
   * The meta data is the labelled data, such as tags, licence and
   * contributors. It is added to the output if
   *   $show_head === $show_meta === true
   * or if
   *   $show_head === false and $show_meta === true.
   * The summary is added to the output if
   *   $show_head === $show_meta === true
   * or if
   *   $show_head === true and $show_meta === false.
   *
   * @param $show_head  bool  If true, the head should be output.
   * If false, the head should not be output.
   * @param $show_meta  bool  If true, the meta data should be
   * output. If false, the meta data should not be output.
   * @param &$line_in_file  string  The line in the readme file
   * currently being parsed. It is passed by reference so that any
   * amendments may be made as necessary.
   * @param $metadata  string[]  The metadata from the head of the
   * file, e.g. tags, version, licence
   * @return bool  True if this line should be added to the output,
   * otherwise false.
   */
  function prp_add_head_meta_data_to_output( bool $show_head, bool $show_meta, string &$line_in_file, array $metadata ): bool {

    // prp_log( 'function', __FUNCTION__ );

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
            $line_in_file = substr( $line_in_file, 0, 14 ) . prp_strip_list( substr( $line_in_file, 14 ), 'c', $metadata[ 'target' ], $metadata[ 'nofollow' ] );
          }

        } else if ( 'Tags:' === substr( $line_in_file, 0, 5 ) ) {
          if ( prp_is_it_excluded( 'tags', $metadata[ 'exclude' ] ) ) {
          // prp_log( __( 'exclude tags', plugin_readme_parser_domain ) );
            $add_to_output = false;
          } else {
            $line_in_file = substr( $line_in_file, 0, 6 ) . prp_strip_list( substr( $line_in_file, 6 ), 't', $metadata[ 'target' ], $metadata[ 'nofollow' ] );
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
