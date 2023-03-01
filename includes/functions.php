<?php
/**
 * Functions
 *
 * Functions called by main output generator
 *
 * @package  Pandammonium-Readme-Parser
 * @since  1.2
 */

if ( !function_exists( 'prp_log' ) ) {
/**
 * Prints a message to the debug log file.
 *
 * @since 2.0.0
 *
 * @param string  message_name  (optional)  A name to associate with the
 * message. This is useful if logging multiple messages.
 * @param string / array $message    The message to be logged.
 * @param bool  $error  Whether the message is about an error or not.
 *
 */
function prp_log( $message_name, $message = '', $error = false, $echo = false ) {

    $debugging = defined( 'WP_DEBUG' ) && WP_DEBUG;
    $log_file = defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG;
    $log_display = defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY;

    // error_log( print_r( '  WP_DEBUG:         ' . ($debugging ? 'true' : 'false' ), true ) );
    // error_log( print_r( '  WP_DEBUG_LOG:     ' . ($log_file ? 'true' : 'false' ), true ) );
    // error_log( print_r( '  WP_DEBUG_DISPLAY: ' . ($log_display ? 'true' : 'false' ), true ) );
    // error_log( print_r( '  error:            ' . ($error ? 'true' : 'false' ), true ) );
    // error_log( print_r( '  echo:             ' . ($echo ? 'true' : 'false' ), true ) );

    // echo '<pre>' .
    //   print_r( 'WP_DEBUG:         ' . ($debugging ? 'true' : 'false' ), true ) . '<br>' .
    //   print_r( 'WP_DEBUG_LOG:     ' . ($log_file ? 'true' : 'false' ), true ) . '<br>' .
    //   print_r( 'WP_DEBUG_DISPLAY: ' . ($log_display ? 'true' : 'false' ), true ) . '<hr>' .
    //   print_r( '$error:           ' . ($error ? 'true' : 'false' ), true ) . '<br>' .
    //   print_r( '$echo:            ' . ($echo ? 'true' : 'false' ), true ) .
    //  '</pre>';

    $prefix = 'PRP | ';
    $header = ( '' === $message_name ) ? '' : $message_name;
    $error_style = $error ? ' class="error"' : '';
    $divider = ( '' === $message ) ? '' : ': ';
    $message_type = gettype( $message );
    $output = '';
    switch ( $message_type ) {
      case 'array':
        $output = print_r( $header, true ) . $divider . print_r( $message, true );
      break;
      default:
        $output = $header . $divider . $message;
      break;
    }

    if ( $debugging ) {
      if ( $log_file ) {
        error_log( $prefix . $output );
      }
      if ( ( $error && $echo ) ||
           $log_display ) {

        $delim = ':';
        $pos = strpos( $output, $delim );
        if ( $pos !== false ) {
          $output = '<b>' . str_replace( $delim, $delim . '</b>', $output );
        }
        if ( 'array' === $message_type ) {
          echo '<pre' . $error_style . '>' . $output . '</pre>';
        } else {
          echo '<p' . $error_style . '>' . $output . '</p>';
        }
      }
    }

  }
}

if ( !function_exists( 'prp_get_readme' ) ) {
/**
 * Get the readme file
 *
 * Function to work out the filename of the readme and get it
 *
 * @since  1.2
 *
 * @param  $plugin_url   string  readme name or URL
 * @return       string  False or array containing readme and plugin name
 */
  function prp_get_readme( $plugin_url, $version = '' ) {

    // prp_log( __( '  Get readme:', plugin_readme_parser_domain ) );
    // prp_log( __( '  title:      \'' . $plugin_url . '\'', plugin_readme_parser_domain ) );

    // Work out filename and fetch the contents

    if ( strpos( $plugin_url, '://' ) === false ) {
      $array[ 'name' ] = str_replace( ' ', '-', strtolower( $plugin_url ) );
      $plugin_url = 'https://plugins.svn.wordpress.org/' . $array[ 'name' ] . '/';
      // prp_log( __( '  url:        \'' . $plugin_url . '\'', plugin_readme_parser_domain ) );
    if ( is_numeric( $version ) ) {
      $plugin_url .= 'tags/' . $version;
      // prp_log( __( '  tag url:    \'' . $plugin_url . '\'', plugin_readme_parser_domain ) );
    } else {
      $plugin_url .= 'trunk';
      // prp_log( __( '  trunk url:  \'' . $plugin_url . '\'', plugin_readme_parser_domain ) );
    }
    $plugin_url .= '/readme.txt';
    // prp_log( __( '  readme.txt: \'' . $plugin_url . '\'', plugin_readme_parser_domain ) );
    }

    $file_data = prp_get_file( $plugin_url );
    // prp_log( __( '  file data:   contents of readme file', plugin_readme_parser_domain ) );
    // prp_log( $file_data, '  file data:' );

    // Ensure the file is valid

    if ( ( $file_data[ 'rc' ] == 0 ) &&
         ( $file_data[ 'file' ] != '' ) &&
         ( substr( $file_data[ 'file' ], 0, 9 ) != '<!DOCTYPE' ) &&
         ( substr_count( $file_data[ 'file' ], "\n" ) != 0 ) ) {

      // Return values

      $array[ 'file' ] = $file_data[ 'file' ];

      return $array;

    } else {

      // prp_log( __( '  readme file is invalid', plugin_readme_parser_domain ) );

      // If not valid, return false

      return false;
    }
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
   * @return       string  true or false, depending on whether the section was valid
   */
  function prp_is_it_excluded( $tofind, $exclude ) {

    $tofind = strtolower( $tofind );
    $return = true;

    // prp_log( __( '  Is \'' . $tofind . '\' excluded?', plugin_readme_parser_domain ) );
    // prp_log( __( '  exclusion list: \'' . $exclude . '\'', plugin_readme_parser_domain ) );


    if ( 'screenshots' === $tofind ||
         'screenshot' === $tofind ) {

      $return = true;

    } else {

      if ( $tofind != $exclude ) {

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
    // if ( 'meta' === $tofind || 'head' === $tofind ) {
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
   * @param  $start_pos    string  Position of line to look from
   * @return       string  Section name
   */
  function prp_get_section_name( $readme_line, $start_pos ) {

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
 * @param  $mirror     string  Array of mirrors
 * @param  $plugin_name  string  Plugin name
 * @return       string  Output
 */
  function prp_display_links( $download, $target, $nofollow, $version, $mirror, $plugin_name ) {

    // prp_log( __( '  Display links:', plugin_readme_parser_domain ) );
    // prp_log( __( '  download link: \'' . $download . '\'', plugin_readme_parser_domain ) );
    // prp_log( __( '  target:        \'' . $target . '\'', plugin_readme_parser_domain ) );
    // prp_log( __( '  nofollow:      \'' . $nofollow . '\'', plugin_readme_parser_domain ) );
    // prp_log( __( '  version:       \'' . $version . '\'', plugin_readme_parser_domain ) );

    $crlf = "\r\n";

    $output = '<div markdown="1" class="np-links">' . $crlf . '## Links ##' . $crlf . $crlf;

    if ( $version != '' ) {
      $output .= '<a class="np-download-link" href="' . $download . '" target="' . $target . '"' . $nofollow . '>Download the latest version</a> (' . $version . ')<br /><br />' . $crlf;

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
  function prp_check_img_exists( $filename, $ext ) {

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
   * @param  $list     string  Provided list
   * @param  $type   string  Type of list
   * @param  $target   string  Link target
   * @param  $nofollow   string  Link nofollow
   * @return       string  HTML output
   */
  function prp_strip_list( $list, $type, $target, $nofollow ) {

    // prp_log( __( '  Strip list:', plugin_readme_parser_domain ) );
    // prp_log( __( '  list:     \'' . $list. '\'', plugin_readme_parser_domain ) );
    // prp_log( __( '  type:     \'' . $type . '\'', plugin_readme_parser_domain ) );
    // prp_log( __( '  target:   \'' . $target . '\'', plugin_readme_parser_domain ) );
    // prp_log( __( '  nofollow: \'' . $nofollow . '\'', plugin_readme_parser_domain ) );

    if ( $type == 'c' ) {
      $url = 'https://profiles.wordpress.org/users/';
    } else if ( $type == 't' ) {
      $url = 'https://wordpress.org/extend/plugins/tags/';
    } else {
      // prp_log( __( 'Invalid type found.', '', plugin_readme_parser_domain ), true );
      $url = '';
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
   * @param  string  $filein   File name to fetch
   * @param  string  $header   Only get headers?
   * @return string    Array containing file contents and response
   */
  function prp_get_file( $filein, $header = false ) {

    // prp_log( __( '  Get file:', plugin_readme_parser_domain ) );
    // prp_log( __( '  file in:     \'' . $filein. '\'', plugin_readme_parser_domain ) );
    // prp_log( __( '  header:      ' . ( $header ? 'true' : 'false' ), plugin_readme_parser_domain ) );

    $rc = 0;
    $error = '';
    if ( $header ) {
      $fileout = wp_remote_head( $filein );
      if ( is_wp_error( $fileout ) ) {
        $error = 'Header: ' . $fileout -> get_error_message();
        $rc = -1;
      }
    } else {
      $fileout = wp_remote_get( $filein );
      if ( is_wp_error( $fileout ) ) {
        $error = 'Body: ' . $fileout -> get_error_message();
        $rc = -1;
      } else {
        if ( isset( $fileout[ 'body' ] ) ) {
        $file_return[ 'file' ] = $fileout[ 'body' ];
        }
      }
    }

    $file_return[ 'error' ] = $error;
    $file_return[ 'rc' ] = $rc;
    if ( !is_wp_error( $fileout ) ) {
      if ( isset( $fileout[ 'response' ][ 'code' ] ) ) {
        $file_return[ 'response' ] = $fileout[ 'response' ][ 'code' ];
      }
    }

    // prp_log( __( '  file out:    contents of readme file', plugin_readme_parser_domain ) );
    // prp_log( $fileout, '  file out:' );
    // prp_log( __( '  file return: contents of readme file', plugin_readme_parser_domain ) );
    // prp_log( $file_return, '  file return:' );

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
   * @return     string  Array of parameters.
   */
  function prp_get_list( $input, $separator = '', $type = '' ) {   // Version 1.2

    // prp_log( __( '  Get \'' . $type . '\' list:', plugin_readme_parser_domain ) );
    // prp_log( __( '  input:     \'' . $input . '\'', plugin_readme_parser_domain ) );
    // prp_log( __( '  separator: \'' . $separator . '\'', plugin_readme_parser_domain ) );

    if ( $separator == '' ) {
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

    if ( $input != '' ) {
      $item++;
      $content[ $item ] = substr( $input, 0 );
    }

    $content[ 0 ] = $item;
    // prp_log( $content[0], '  content[0]:' );
    return $content;
  }
}

if ( !function_exists( 'prp_normalise_parameters' ) ) {
/**
 * Normalises the quotation marks to straight ones from curly ones.
 * Fixes the erroneous array member created by having a space in 'Upgrade
 * Notice'.
 *
 * @param  $text  string  The text to normalise the quotation marks in.
 * @return        string  The text containing normalised quotation marks.
 */
  define( 'QUOTES', array(
   '“' => '',
   '”' => '',
   '‘' => '',
   '’' => '',
   '&#8220;' => '',
   '&#8221;' => ''
  ) );
  function prp_normalise_parameters( $text ) {

    if ( is_string( $text ) ) {
      $normalised_text = str_replace(array_keys(QUOTES), array_values(QUOTES), $text);
      // prp_log( __( 'Normalised ', plugin_readme_parser_domain ) . $text );
      // prp_log( __( '        to ', plugin_readme_parser_domain ) . $normalised_text  );
      return $normalised_text;

    } else if ( is_array($text ) ) {
      $normalised_text = array();
      foreach ( $text as $key => $value ) {
        // prp_log( $key . ': ' . $value );
        $normalised_text[$key] = str_replace(array_keys(QUOTES), array_values(QUOTES), $text[$key]);
        // prp_log( $key . ': ' . $normalised_text[$key] );
      }
      if ( isset( $normalised_text[0] ) ) {
        if ( isset( $normalised_text[ 'exclude' ] ) ) {
          $normalised_text['exclude'] .= ' ' . $normalised_text[0];
        } else if ( isset( $normalised_text[ 'include' ] ) ) {
          $normalised_text['include'] .= ' ' . $normalised_text[0];
        } else {
          // prp_log( __( 'Erroneous parameter found', plugin_readme_parser_domain ) );
        }
        unset( $normalised_text[0] );
      }
      return $normalised_text;

    } else {
      // prp_log( $text, 'Normalise: wanted a string or an array; got \'' . gettype( $text ) . '\':'  );
      return $text;
    }
  }
}

if ( !function_exists( 'prp_toggle_global_shortcodes' ) ) {
/**
 * Toggle the shortcodes so that any shortcodes in the readme file
 * aren't expanded. Expanded shortcodes in the readme files cause problems if
 * they're used to provide examples of use of the plugin's shortcode.
 *
 * Some plugins change this filter’s priority, so clear the global list of
 * registered shortcodes temporarily, except for this plugin's readme_info
 * and readme_banner, which are needed.
 *
 * @since  2.0.0
 * @link   https://wordpress.stackexchange.com/a/115176
 *
 * @param  $content     array  The readme file content
 * @param  $exceptions  array  The shortcodes to keep active
 * @return array  The readme file content
 */
  function prp_toggle_global_shortcodes( $content ) {

    $file = plugin_dir_path( __DIR__ );
    // prp_log( __( 'Plugin directory: ', plugin_readme_parser_domain ) . $file );
    if ( str_contains( $file, plugin_readme_parser_filename ) ) {

      static $original_shortcodes = array();

      // prp_log( __( '# original shortcodes: ', plugin_readme_parser_domain ) . count ( $original_shortcodes ) );
      // prp_log( __( '# global shortcodes:   ' . count ( $GLOBALS['shortcode_tags', plugin_readme_parser_domain )] ) );

      // prp_log( __( 'Shortcode content: ', plugin_readme_parser_domain ) . $content );

      if ( count ( $original_shortcodes ) === 0 ) {
        // Toggle the shortcodes OFF

        $original_shortcodes = $GLOBALS['shortcode_tags'];
        $GLOBALS['shortcode_tags'] = array();

        $current_theme_supports_blocks = wp_is_block_theme();

        if ( $current_theme_supports_blocks ) {
          // prp_log( __( 'This theme DOES support blocks', plugin_readme_parser_domain ) );
          //   // prp_log( __( 'Toggling ALL global shortcodes OFF', plugin_readme_parser_domain ) );
          if  ( str_contains( $content, '[readme_info' ) ) {
            // prp_log( __( 'Content contains \'[readme_info\'', plugin_readme_parser_domain ) );
            $GLOBALS['shortcode_tags']['readme_info'] = 'readme_info';
            // prp_log( __( 'Toggling global shortcodes OFF except for:', plugin_readme_parser_domain ) );
            // prp_log( $GLOBALS['shortcode_tags'], 'Global shortcodes:' );
          }

        } else {
          // prp_log( __( 'This theme DOES NOT support blocks', plugin_readme_parser_domain ) );

          // Need to put some of this plugin's ones back, otherwise it all breaks; it's unclear as to why and as to why these combinations work:

          if ( ( str_contains( $content, '[readme ' ) ) ||
               ( str_contains( $content, '[readme]' ) ) ) {
            // prp_log( __( 'Content contains \'[readme \' or \'[readme]\'', plugin_readme_parser_domain ) );

            $GLOBALS['shortcode_tags']['readme'] = 'readme_parser';
            $GLOBALS['shortcode_tags']['readme_info'] = 'readme_info';
            $GLOBALS['shortcode_tags']['readme_banner'] = 'readme_banner';

          } else if  ( str_contains( $content, '[readme_info' ) ) {
            // prp_log( __( 'Content contains \'[readme_info\'', plugin_readme_parser_domain ) );

            $GLOBALS['shortcode_tags']['readme_info'] = 'readme_info';

          } else if  ( str_contains( $content, '[readme_banner' ) ) {
            // prp_log( __( 'Content contains \'[readme_banner\'', plugin_readme_parser_domain ) );

            // Need to check this combo once banner display is working.

            $GLOBALS['shortcode_tags']['readme'] = 'readme_parser';
            $GLOBALS['shortcode_tags']['readme_banner'] = 'readme_banner';
            $GLOBALS['shortcode_tags']['readme_info'] = 'readme_info';

          } else {
            // prp_log( __( 'Failed to find Plugin-readme Parser shortcode', plugin_readme_parser_domain ) );

            // We're in the wild, not writing out a readme with this plugin, so all the shortcodes need to be functional:
            // prp_log( __( 'Toggling ALL global shortcodes ON', plugin_readme_parser_domain ) );
            // prp_log( __( '# original shortcodes: ', plugin_readme_parser_domain ) . count ( $original_shortcodes ) );
            // prp_log( __( '# global shortcodes:   ' . count ( $GLOBALS['shortcode_tags'] ), plugin_readme_parser_domain ) );
            $GLOBALS['shortcode_tags'] = $original_shortcodes;
            return $content;

          }

          // prp_log( __( 'Toggling global shortcodes OFF except for:', plugin_readme_parser_domain ) );
          // prp_log( $GLOBALS['shortcode_tags'], 'Global shortcodes:' );
        }

      } else {
        // Toggle the shortcodes ON

        // prp_log( __( 'Toggling global shortcodes ON', plugin_readme_parser_domain ) );

        $GLOBALS['shortcode_tags'] = $original_shortcodes;
        $original_shortcodes = array();
        // prp_log( __( 'Repopulating GLOBAL shortcodes with original shortcodes', plugin_readme_parser_domain ) );

      }
    } else {
      prp_report_error( __( 'wrong plugin supplied', plugin_readme_parser_domain), plugin_readme_parser_name );
      // prp_log( __( '***** Wrong plugin supplied *****', plugin_readme_parser_domain ) );
    }
    return $content;
  }

  add_filter( 'the_content', 'prp_toggle_global_shortcodes', -1 );
  add_filter( 'the_content', 'prp_toggle_global_shortcodes', PHP_INT_MAX );
}

if ( !function_exists( 'prp_add_head_to_output' ) ) {
  /**
   * Determine which parts of the head, if nay, should be added to the output.
   *
   * The head comprises the plugin title/name, the meta data and a summary/
   * description of the plugin. There may be one or more blank lines.
   *
   * The title is never displayed.
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
   * @param $show_head  boolean  If true, the head should be output. If false,
   * the head should not be output.
   * @param $show_meta  boolean  If true, the meta data should be
   * output, even if the rest of the head should not be output. If false, the
   * meta data should not be output, even if the rest of the head should be
   * output.
   * @param &$line_in_file  string  The line in the readme file currently
   * being parsed. It is passed by reference so that any amendments may be
   * made.
   * @return boolean  True if this line should be added to the output,
   * otherwise false.
   */
  function prp_add_head_to_output( $show_head, $show_meta, &$line_in_file, $metadata ) {
    $add_to_output = true;

    prp_log( __( 'show head', plugin_readme_parser_domain ), ( $show_head ? 'true' : 'false' ) );
    prp_log( __( 'show meta', plugin_readme_parser_domain ), ( $show_meta ? 'true' : 'false' ) );

    if ( $show_head ) {
      // At least some of the head is to be output.
      if ( $show_meta ) {
        // prp_log( __( 'INC META, INC SUMMARY', plugin_readme_parser_domain ) );
        // Add the full head to the output:
        if ( prp_line_is_head_meta_data( $line_in_file ) ) {
          $add_to_output = prp_add_head_meta_data_to_output( $show_head, $show_meta, $line_in_file );
          if ( !$add_to_output ) {
            if ( '' === $line_in_file[ 0 ] ) {
              $add_to_output = false;
            } else {
              $add_to_output = true;
            }
          }
        } else {
          if ( '' === $line_in_file[ 0 ] ) {
            $add_to_output = false;
          } else {
            $add_to_output = true;
          }
        }
      } else {
        // prp_log( __( 'INC META, EXC SUMMARY', plugin_readme_parser_domain ) );
        // Add the summary only
        if ( prp_line_is_head_meta_data( $line_in_file ) ) {
          $add_to_output = false;
        } else {
          if ( '' === $line_in_file ) {
            $add_to_output = false;
          } else {
            $add_to_output = true;
          }
        }
      }
    } else {
      if ( $show_meta ) {
        // prp_log( __( 'ADDING META DATA ONLY', plugin_readme_parser_domain ) );
        // Add the head but not the meta data to the output:
        $add_to_output = prp_add_head_meta_data_to_output( $show_head, $show_meta, $line_in_file );
      } else {
        // prp_log( __( 'ADDING NO HEAD', plugin_readme_parser_domain ) );
        // Add nothing to the output.
        $add_to_output = false;
      }
    }
    // prp_log( __( 'add to output', plugin_readme_parser_domain ), ( $add_to_output ? 'true' : 'false' ) );

    return $add_to_output;
  }
}

if ( !function_exists( 'prp_line_is_head_meta_data' ) ) {
  /**
   * Tests to see whether the current line in the readme file is a line in the head meta data (e.g. tags, licence, contributors) or not.
   *
   * @param $line_in_file  string  The current line of the readme file being
   * parsed.
   * @return boolean  Returns true of the current line in the readme file is
   * part of the head meta data, otherwise false.
   */
  function prp_line_is_head_meta_data( $line_in_file ) {
    if ( ( 'Contributors:' == substr( $line_in_file, 0, 13 ) ) or
         ( 'Donate link:' == substr( $line_in_file, 0, 12 ) ) or
         ( 'Tags:' == substr( $line_in_file, 0, 5 ) ) or
         ( 'Requires at least:' == substr( $line_in_file, 0, 18 ) ) or
         ( 'Requires PHP:' == substr( $line_in_file, 0, 13 ) ) or
         ( 'Tested up to:' == substr( $line_in_file, 0, 13 ) ) or
         ( 'Stable tag:' == substr( $line_in_file, 0, 11 ) ) or
         ( 'License URI:' == substr( $line_in_file, 0, 12 ) ) or
         ( 'License:' == substr( $line_in_file, 0, 8 ) ) ) {
      return true;
    } else {
      return false;
    }
  }
}

if ( !function_exists( 'prp_add_head_meta_data_to_output' ) ) {
  function prp_add_head_meta_data_to_output( $show_head, $show_meta, &$line_in_file, $metadata ) {
    $add_to_output = true;

    // Process meta data from top

    if ( $show_head ||
         $show_meta ) {
      if ( prp_line_is_head_meta_data( $line_in_file ) ) {

        if ( !$show_meta ) {
          prp_log( __( 'exclude all meta', plugin_readme_parser_domain ) );
          $add_to_output = false;

        } else if ( ( 'Requires at least:' == substr( $line_in_file, 0, 18 ) ) &&
             ( prp_is_it_excluded( 'requires', $metadata[ 'exclude' ] ) ) ) {
          prp_log( __( 'exclude WP req', plugin_readme_parser_domain ) );
          $add_to_output = false;

        } else if ( ( 'Requires PHP:' == substr( $line_in_file, 0, 18 ) ) &&
             ( prp_is_it_excluded( 'requires php', $metadata[ 'exclude' ] ) ) ) {
          prp_log( __( 'exclude PHP req', plugin_readme_parser_domain ) );
          $add_to_output = false;

        } else if ( ( 'Tested up to:' == substr( $line_in_file, 0, 13 ) ) &&
             ( prp_is_it_excluded( 'tested', $metadata[ 'exclude' ] ) ) ) {
          prp_log( __( 'exclude test', plugin_readme_parser_domain ) );
          $add_to_output = false;

        } else if ( ( 'License:' == substr( $line_in_file, 0, 8 ) ) &&
             ( prp_is_it_excluded( 'license', $metadata[ 'exclude' ] ) ) ) {
          prp_log( __( 'exclude licence', plugin_readme_parser_domain ) );
          $add_to_output = false;

        } else if ( 'Contributors:' == substr( $line_in_file, 0, 13 ) ) {
          if ( prp_is_it_excluded( 'contributors', $metadata[ 'exclude' ] ) ) {
          prp_log( __( 'exclude contrib', plugin_readme_parser_domain ) );
            $add_to_output = false;
          } else {
            // Show contributors and tags using links to WordPress pages
            $line_in_file = substr( $line_in_file, 0, 14 ) . prp_strip_list( substr( $line_in_file, 14 ), 'c', $metadata[ 'target' ], $metadata[ 'nofollow' ] );
          }

        } else if ( 'Tags:' == substr( $line_in_file, 0, 5 ) ) {
          if ( prp_is_it_excluded( 'tags', $metadata[ 'exclude' ] ) ) {
          prp_log( __( 'exclude tags', plugin_readme_parser_domain ) );
            $add_to_output = false;
          } else {
            $line_in_file = substr( $line_in_file, 0, 6 ) . prp_strip_list( substr( $line_in_file, 6 ), 't', $metadata[ 'target' ], $metadata[ 'nofollow' ] );
          }

        } else if ( 'Donate link:' == substr( $line_in_file, 0, 12 ) ) {
          if ( prp_is_it_excluded( 'donate', $metadata[ 'exclude' ] ) ) {
          prp_log( __( 'exclude donate', plugin_readme_parser_domain ) );
            $add_to_output = false;
          } else {
            // Convert the donation link to a hyperlink
            $text = substr( $line_in_file, 13 );
            $line_in_file = substr( $line_in_file, 0, 13 ) . '<a href="' . $text . '">' . $text . '</a>';
          }

        } else if ( 'License URI:' == substr( $line_in_file, 0, 12 ) ) {
          if ( prp_is_it_excluded( 'license uri', $metadata[ 'exclude' ] ) ) {
          prp_log( __( 'exclude lic uri', plugin_readme_parser_domain ) );
            $add_to_output = false;
          } else {
            // Convert the licence URL to a hyperlink
            $text = substr( $line_in_file, 13 );
            $line_in_file = substr( $line_in_file, 0, 13 ) . '<a href="' . $text . '">' . $text . '</a>';
          }

        } else if ( 'Stable tag:' == substr( $line_in_file, 0, 11 ) ) {
          if ( prp_is_it_excluded( 'stable', $metadata[ 'exclude' ] ) ) {
          prp_log( __( 'exclude stab tag', plugin_readme_parser_domain ) );
            $add_to_output = false;
          } else {
            // Link to the download given by the version
            $line_in_file = substr( $line_in_file, 0, 12 ) . '<a href="' . $metadata[ 'download' ].'" style="max-width: 100%;">' . $metadata[ 'version' ] . '</a>';
          }
        }

        // If one of the header tags, add a BR tag to the end of the line.
        // As the output is meant to be XHTML, the BR tag needs to be closed. The proper way to do this is to have no space before the slash.

        $line_in_file .= '<br/>';
      }
    } else {
      $add_to_out = false;
    }

    prp_log( __( 'add head meta data to output', plugin_readme_parser_domain ), ( $add_to_output ? 'true' : 'false' ) );
    return $add_to_output;
  }
}

if ( !function_exists( 'prp_report_error' ) ) {
  /**
   * Report an error (1.4)
   *
   * Logs an error message along with the plugin name and the error text, and
   * returns an HTML-formatted error message.
   *
   * @since  1.0
   *
   * @param  $plugin_domain  string  The domain of the plugin where the error
   * occurred.
   * @param  $error    string  The error message to report.
   * @param  $echo   string  A boolean value indicating whether to output the
   * error message immediately using echo. If false, the function returns the
   * formatted error message instead of echoing it.
   * @return     string / true  If $echo === true, the function outputs the error message using echo and returns true. If $echo is false, the function returns the formatted error message instead of echoing it.
   */
  function prp_report_error( $plugin_name, $error, $echo = true ) {

    // prp_log( $error, $plugin_name, true, $echo );

    $output = '<p class="error">' . $plugin_name . ': ' . $error . '</p>';

    if ( $echo ) {
      echo $output;
      return true;
    } else {
      return $output;
    }

  }
}
?>
